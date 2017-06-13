var errorTextArray = [];

function pushErrorIfNotExist(error) {
  if (errorTextArray.indexOf(error) == -1)
    errorTextArray.push(error);
  checkError();
}

function removeErrorIfExists(error) {
  var index = errorTextArray.indexOf(error);
  if (index != -1)
    errorTextArray.splice(index, 1);
  checkError();
}

function checkError() {
  var el = document.getElementById("forum-group-errorbox");
  
  if (errorTextArray.length > 0) {
    //Set error messgae
    var fullString = errorTextArray.join("<br />");
    document.getElementById("errorMessage").innerHTML = fullString;
    
    el.classList.add("active");
  } else {
    el.classList.remove("active");
  }
}

function checkSteamId64() {
  var steamid64 = document.getElementById("inputsteamid64").value;
  var res = steamid64.match(/^[0-9]{17}$/g);
  
  var el = document.getElementById("forum-group-steamid64");
  var error = "SteamID64 is invalid. Must be a 17 digit length number.";
  
  if (res !== null && res !== undefined) {
    //Good steamid64
    el.classList.remove("has-error");
    el.classList.add("has-success");
    
    removeErrorIfExists(error);
  } else {
    el.classList.remove("has-success");
    el.classList.add("has-error");
    
    pushErrorIfNotExist(error);
  }
}

function checkPassword() {
  var password = document.getElementById("inputPassword").value;
  var el = document.getElementById("forum-group-password");
  var error = "You must provide a non-empty password.";
  
  if (password == null || password == undefined || password.length == 0) {
    el.classList.remove("has-success");
    el.classList.add("has-error");
    pushErrorIfNotExist(error);
  } else {
    el.classList.remove("has-error");
    el.classList.add("has-success");
    removeErrorIfExists(error);
  }
}

function checkCheckbox() {
  var checkbox = document.getElementById("inputCheckbox");
  var el = document.getElementById("forum-group-checkbox");
  var error = "You must check the permission checkbox to use this tool.";

  if (checkbox.checked) {
    el.classList.remove("has-error");
    el.classList.add("has-success");
    removeErrorIfExists(error);
    return true;
  } else {
    el.classList.remove("has-success");
    el.classList.add("has-error");
    pushErrorIfNotExist(error);
    return false;
  }
}

function checkForm() {
  checkCheckbox();
  checkSteamId64();
  checkPassword();
  checkError();
  
  if (errorTextArray.length > 0) {
    return false;
  }
  
  return true;
}

function resetForm() {
  //empty error text array
  errorTextArray.splice(0, errorTextArray.length);
  
  //reset status
  document.getElementById("forum-group-steamid64").classList.remove("has-error");
  document.getElementById("forum-group-steamid64").classList.remove("has-success");
  
  document.getElementById("forum-group-password").classList.remove("has-error");
  document.getElementById("forum-group-password").classList.remove("has-success");
  
  document.getElementById("forum-group-checkbox").classList.remove("has-error");
  document.getElementById("forum-group-checkbox").classList.remove("has-success");
  
  checkError();
}