// STORING AND RETRIEVING DATA //
// Values of fields in the contact form are stored on change

for (input of document.querySelectorAll("form input, form select, form textarea")) {
    if (input.type == "text" && input.name != "subject" || input.type == 'date' || input.type == 'number' || input.type == "textarea") {
        if (localStorage.getItem(input.name))
            input.value = localStorage.getItem(input.name);

        input.addEventListener("change", function () {
            localStorage.setItem(this.name, this.value);
            //console.log(localStorage);
        })
    } 

    else if (input.type == 'radio') {
        if (localStorage.getItem(input.name) == input.value)
            input.checked = true;

        input.addEventListener("change", function () {
            localStorage.setItem(this.name, this.value);
            //console.log(localStorage);
        })
    }
    else if (input.tagName == 'SELECT') {
        if (localStorage.hasOwnProperty(input.name))
            input.selectedIndex = localStorage.getItem(input.name);
        input.addEventListener("change", function () {
            localStorage.setItem(this.name, this.selectedIndex);
        });
    }
    else if (input.type == 'checkbox') {
        if (localStorage.hasOwnProperty(input.name))
            input.checked = eval(localStorage.getItem(input.name + "_" + input.value));
        input.addEventListener("change", function() {
            localStorage.setItem(this.name + "_" + this.value, this.checked);
        });
    }
    //else
    //    console.error(`This type of input isn't handled :\n${input.tagName} ${input.type}`)
}
