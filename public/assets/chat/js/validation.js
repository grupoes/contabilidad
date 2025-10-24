(function($) {
    "use strict";
	
	if($('#passwordInput').length > 0) {
	let passwordInput = document.querySelector('#passwordInput input[type="password"]');
    let passwordStrength= document.getElementById('passwordStrength');
    let poor = document.querySelector('#passwordStrength #poor');
    let weak = document.querySelector('#passwordStrength #weak');
    let strong = document.querySelector('#passwordStrength #strong');
    let heavy = document.querySelector('#passwordStrength #heavy');
    let passwordInfo = document.getElementById('passwordInfo');
    let passcheck = document.querySelector('#passwordInput .pass-checked');
  
    let poorRegExp = /[a-z]/;
    let weakRegExp = /(?=.*?[0-9])/;;
    let strongRegExp = /(?=.*?[#?!@$%^&*-])/;
  
	let whitespaceRegExp = /^$|\s+/;
 
	

    passwordInput.oninput= function(){
   
        let passwordValue= passwordInput.value;
        let passwordLength= passwordValue.length;
        let poorPassword= passwordValue.match(poorRegExp);
        let weakPassword= passwordValue.match(weakRegExp);
        let strongPassword= passwordValue.match(strongRegExp);
        let whitespace= passwordValue.match(whitespaceRegExp);
		if(passwordValue != ""){
			passwordStrength.style.display = "block";
			passwordStrength.style.display = "flex";
			passwordInfo.style.display = "block";
			passwordInfo.style.color = "black";
			if(whitespace)
			{
				passwordInfo.textContent = "whitespaces are not allowed";
			}
			else {
				poorPasswordStrength(passwordLength, poorPassword, weakPassword, strongPassword);
				weakPasswordStrength(passwordLength, poorPassword, weakPassword, strongPassword);
				strongPasswordStrength(passwordLength, poorPassword, weakPassword, strongPassword);
				heavyPasswordStrength(passwordLength, poorPassword, weakPassword, strongPassword);
			}
			
		}
		else {
			   passwordInfo.style.display = "none";
			   passwordStrength.classList.remove("poor-active");
			   passwordStrength.classList.remove("avg-active");
			   passwordStrength.classList.remove("strong-active");
			   passwordStrength.classList.remove("heavy-active");
			   passcheck.classList.remove("active");
			
		   }
		}
		
		function poorPasswordStrength(passwordLength, poorPassword, weakPassword, strongPassword){
			if(passwordLength < 8)
			{
			   poor.classList.add("active");
			   passwordStrength.classList.add("poor-active");
			   passwordStrength.classList.remove("avg-active");
			   passwordStrength.classList.remove("strong-active");
			   passwordStrength.classList.remove("heavy-active");
			   passwordInfo.style.display = "block";
			   passwordInfo.style.color = "#FF0000";
				  
			}
		}

		function weakPasswordStrength(passwordLength, poorPassword, weakPassword, strongPassword){
			if(passwordLength >= 8 && (poorPassword || weakPassword || strongPassword))
			{
			   weak.classList.add("active");
			   passwordStrength.classList.remove("poor-active");
			   passwordStrength.classList.add("avg-active");
			   passwordStrength.classList.remove("strong-active");
			   passwordStrength.classList.remove("heavy-active");
			   passwordInfo.style.display = "block";
			   passwordInfo.style.color = "#FFB54A";			  
				  
			}else{
			 weak.classList.remove("active");
			 
		   }
		}

		function strongPasswordStrength(passwordLength, poorPassword, weakPassword, strongPassword){
		   if(passwordLength>= 8 && poorPassword && (weakPassword || strongPassword))
			{
			 strong.classList.add("active");
			 passwordStrength.classList.remove("avg-active");
			 passwordStrength.classList.remove("poor-active");
			 passwordStrength.classList.add("strong-active");
			 passwordStrength.classList.remove("heavy-active");
			 passwordInfo.style.color = "#1D9CFD";
		   
		   }else{
			 strong.classList.remove("active");
			 
		   }
		}
		
		function heavyPasswordStrength(passwordLength, poorPassword, weakPassword, strongPassword){
		  if(passwordLength >= 8 && (poorPassword && weakPassword) && strongPassword)
			{
			 heavy.classList.add("active");
			 passwordStrength.classList.remove("poor-active");
			 passwordStrength.classList.remove("avg-active");
			 passwordStrength.classList.remove("strong-active");
			 passwordStrength.classList.add("heavy-active");
			 passcheck.classList.add("active");
			 passwordInfo.style.color = "#159F46";
		   }else{
			 heavy.classList.remove("active");
			 passcheck.classList.remove("active");
			 
		   }
		}
	}	
		
		
		
	
})(jQuery);