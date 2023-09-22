/*
TODO: we need to finish our CRUD operations && implement hashing our passwords
*/
const urlBase = 'http://localhost/API';
const extension = 'php';

let userId = 0;
let firstName = "";
let lastName = "";

function doLogin(){
	userId = 0;
	firstName = "";
	lastName = "";
	
	let login = document.getElementById("loginName").value;
	let password = document.getElementById("loginPassword").value;
//	var hash = md5( password );
//This is where we will hash our passwords
//lets try to not use md5 bc its weak
	
	document.getElementById("loginResult").innerHTML = "";

	let tmp = {login:login,password:password};
//	var tmp = {login:login,password:hash};
//^^^we would input our hashed password instead of the original password
	let jsonPayload = JSON.stringify( tmp );
	
	let url = urlBase + '/login.' + extension;

	let xhr = new XMLHttpRequest();
	xhr.open("POST", url, true);
	xhr.setRequestHeader("Content-type", "application/json; charset=UTF-8");
	try {
		xhr.onreadystatechange = function() 
		{
			console.log(`logging ready state ${this.readyState}`);
			if (this.readyState == 4 && this.status == 200){
				console.log(`logging responseText ${xhr.responseText}`);
				let jsonObject = JSON.parse( xhr.responseText );
				userId = jsonObject.id;
		
				if( userId < 1 ){		
					document.getElementById("loginResult").innerHTML = "User/Password incorrect :(";
                    console.log("Login Failed");
					return;
				}

                document.getElementById("loginResult").innerHTML = "You Succeeded!";
				firstName = jsonObject.firstName;
				lastName = jsonObject.lastName;

                console.log("Successful Login");
				saveCookie();
	
				window.location.href = "mainPage.html";
			}
		};
		xhr.send(jsonPayload);
	} catch(err){
		document.getElementById("loginResult").innerHTML = err.message;
	}
}

//TODO: fix doSignup()
/*
function doSignup(){
	let signupFirstName = document.getElementById("firstName").value;
	let signupLastName = document.getElementById("lastName").value;

	let username = document.getElementById("username").value;
	let password = document.getElementById("password").value;
	//TODO: here would be where we hash the password

	//NOTE: this block of code is temporary while we test out & nuild the backend
	if (!signupFirstName || !signupLastName || !username || !password){
	  document.getElementById("signupResult").innerHTML ="Must fill all fields";
	  return;
	}
	//end of temporary check

	let tmp = {firstname:signupFirstName, lastame:signupLastName, login:username, password:password};
	//^^^in the future the password passed in would be the hashed password
	let jsonPayload = JSON.stringify(tmp);

	let url = urlBase + "/signup." + extension;

	let xhr = new XMLHttpRequest();
	xhr.open("POST", url, true);
	xhr.setRequestHeader("Content-type", "application/json; charset=UTF-8");
	try{
		xhr.onreadystatechange = function() 
		{
			if(this.readyState !=4){
				return;
			}
			if(this.status == 409){ //409 implies Conflict, this user already exists in our db
				document.getElementById("signupResult").innerHTML = "User already exists";
				return;//cannot complete our signup
			}
			if(this.status == 200){ //then we are good to go, we already verifired readyState as 4
				let jsonObject = JSON.parse( xhr.responseText );
				userId = jsonObject.id;

				document.getElementById("signupResult").innerHTML = "Welcome to Starfinder!";
				firstName = jsonObject.firstName;
				lastName = jsonObject.lastName;

				console.log("Successful Signup");
				saveCookie();
				// consider when user signsup they auto log in
			}
		};
		xhr.send(jsonPayload);
	} catch (err){
		document.getElementById("signupResult").innerHTML = err.message;
	}
}*/

function saveCookie(){
	let minutes = 20;
	let date = new Date();
	date.setTime(date.getTime()+(minutes*60*1000));	
	document.cookie = "firstName=" + firstName + ",lastName=" + lastName + ",userId=" + userId + ";expires=" + date.toGMTString();
}

function readCookie(){
	userId = -1;
	let data = document.cookie;
	let splits = data.split(",");
	for(var i = 0; i < splits.length; i++) {
		let thisOne = splits[i].trim();
		let tokens = thisOne.split("=");
		if( tokens[0] == "firstName" ){
			firstName = tokens[1];
		}
		else if( tokens[0] == "lastName" ){
			lastName = tokens[1];
		}
		else if( tokens[0] == "userId" ){
			userId = parseInt( tokens[1].trim() );
		}
	}
	
	if( userId < 0 ){
		window.location.href = "signup.html";
	}
	else {
		document.getElementById("userName").innerHTML = "Logged in as " + firstName + " " + lastName;
	}
}

function doLogout(){
	userId = 0;
	firstName = "";
	lastName = "";
	document.cookie = "firstName= ; Expires = Thu, 01 Jan 2025 00:00:00 GMT"; //TODO: change this to abetter format
	window.location.href = "signup.html";
}


function addContact(){
	let newContact = document.getElementById("contactText").value;
	document.getElementById("contactAddResult").innerHTML = "";

	let tmp = {contact:newContact,userId,userId};
	let jsonPayload = JSON.stringify( tmp );

	let url = urlBase + '/addContact.' + extension;
	
	let xhr = new XMLHttpRequest();
	xhr.open("POST", url, true);
	xhr.setRequestHeader("Content-type", "application/json; charset=UTF-8");
	try{
		xhr.onreadystatechange = function() 
		{
			if (this.readyState == 4 && this.status == 200) {
				document.getElementById("contactAddResult").innerHTML = "Contact has been added";
			}
		};
		xhr.send(jsonPayload);
	}
	catch(err){
		document.getElementById("contactAddResult").innerHTML = err.message;
	}
}

//TODO: deleteContact(), updateContact(), searchForContact()
