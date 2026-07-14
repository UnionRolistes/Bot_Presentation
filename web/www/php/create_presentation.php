<?php
// UR_Bot © 2020 by "Association Union des Rôlistes & co" is licensed under Attribution-NonCommercial-ShareAlike 4.0 International (CC BY-NC-SA)
// To view a copy of this license, visit http://creativecommons.org/licenses/by-nc-sa/4.0/
// Ask a derogation at Contact.unionrolistes@gmail.com

// Remplace l'ancien CGI Python (cgi/pres/create_presentation.py). Contrairement
// à Planning, il n'y a pas d'API intermédiaire ici : un webhook Discord n'est
// qu'un POST JSON, pas besoin de discord.py/CGI pour ça. Portage fidèle du
// templating conditionnel ligne par ligne (voir pres_template.txt) et de
// l'envoi — y compris le fait que la validation des champs (verify_data côté
// Python) était déjà désactivée dans le code d'origine (if (False): ...),
// pas de comportement nouveau ajouté ici.

function redirect_and_exit($status, $extra = '') {
    header("Location: http://presentation.unionrolistes.fr?error={$status}{$extra}", true, 303);
    exit;
}

$checks = [];
if (!empty($_POST['news'])) $checks[] = '**News: ** ✅';
if (!empty($_POST['gn'])) $checks[] = '**GN: ** ☑';

$mjs = [];
if (!empty($_POST['MJ'])) $mjs[] = 'MJ';
if (!empty($_POST['PJ'])) $mjs[] = 'PJ';

$os = [];
if (!empty($_POST['win'])) $os[] = ':w10:';
if (!empty($_POST['mac'])) $os[] = '🍎';
if (!empty($_POST['linux'])) $os[] = ':linux:';
if (!empty($_POST['android'])) $os[] = ':android:';

$kwargs = [
    'pseudo' => '<@' . ($_POST['user_id'] ?? '') . '> [' . ($_POST['pseudo'] ?? '') . ']',
    'home' => ($_POST['region'] ?? '') . (!empty($_POST['ville']) ? ' - ' . $_POST['ville'] : ''),
    'age' => !empty($_POST['age']) ? $_POST['age'] : ($_POST['trancheAge'] ?? ''),
    'experience' => $_POST['experience'] ?? '',
    'origin' => $_POST['connaissance'] ?? '',
    'hobby' => $_POST['hobby'] ?? '',
    'mj_pj' => implode('  et  ', $mjs),
    'jdr' => $_POST['JDR'] ?? '',
    'i_like' => $_POST['like'] ?? '',
    'i_dislike' => $_POST['dislike'] ?? '',
    'availability' => $_POST['dispos'] ?? '',
    'secouriste' => $_POST['secouriste'] ?? '',
    'os' => implode(' ', $os),
    'jobs' => $_POST['job'] ?? '',
    'other' => $_POST['autre'] ?? '',
    'free_expression' => $_POST['expression'] ?? '',
    'checks' => implode('  **|**  ', $checks),
];

function render_template($path, $kwargs) {
    $out = '';
    foreach (file($path) as $line) {
        if (preg_match('/^ *#/', $line)) {
            continue; // ligne de commentaire, jamais incluse
        }
        preg_match_all('/\{([A-Za-z0-9_]*)\}/', $line, $matches);
        $fields = $matches[1];
        $is_field = count($fields) > 0;
        $is_empty = true;
        foreach ($fields as $key) {
            if (!empty($kwargs[$key])) {
                $is_empty = false;
                break;
            }
        }
        if (!$is_empty || !$is_field) {
            $replacements = [];
            foreach ($fields as $key) {
                $replacements['{' . $key . '}'] = $kwargs[$key] ?? '';
            }
            $out .= strtr($line, $replacements);
        }
    }
    return $out;
}

$content = render_template(__DIR__ . '/templates/pres_template.txt', $kwargs);

$webhookUrl = $_POST['webhook_url'] ?? '';
if ($webhookUrl === '') {
    redirect_and_exit('envoi');
}

$ch = curl_init($webhookUrl);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode(['content' => $content]),
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    CURLOPT_TIMEOUT => 10,
]);
$response = curl_exec($ch);
$curlErrored = curl_errno($ch) !== 0;
$statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($curlErrored || $statusCode >= 300) {
    error_log("Échec de l'envoi de la présentation : status={$statusCode} response={$response}");
    redirect_and_exit('envoi');
}

redirect_and_exit('isPosted');
