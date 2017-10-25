function onChange(){
    var x = document.getElementById("operation").value;
    var options = document.getElementsByTagName("fieldset");
    for(var i = 0; i<options.length; i++){
        document.getElementsByTagName("fieldset")[i].style.display = "none";
    }
    if(x!="default"){
        document.getElementById(x).style.display = "block";
    }
}

function prodUpdateSelectorOnChange(){
    var x = document.getElementById("prod_update_catid").value;
    var divs = document.getElementsByClassName("prod_update_old_name");
    for(var i = 0; i<divs.length; i++){
        document.getElementsByClassName("prod_update_old_name")[i].style.display = "none";
    }
    if(x!="default"){
        var divName = "prod_update_old_name_" + x;
        document.getElementById(divName).style.display = "block";
    }
}

function prodDeleteSelectorOnChange(){
    var x = document.getElementById("prod_delete_catid").value;
    var divs = document.getElementsByClassName("prod_delete_old_name");
    for(var i = 0; i<divs.length; i++){
        document.getElementsByClassName("prod_delete_old_name")[i].style.display = "none";
    }
    if(x!="default"){
        var divName = "prod_delete_old_name_" + x;
        document.getElementById(divName).style.display = "block";
    }
}