function CalcNeighbours(m,n){
	
	var x;
	var y;
	
	var q = 0;
	
	for (let k = -1; k <= 1; k++){
		
		for (let l = -1; l <= 1; l++){
			
			if ((m + k >= 0) && (m + k <= 15)){ 
			
				x = m + k; 
			
			} else{
				
				if (m + k < 0){ x = 15; }
				if (m + k > 15){ x = 0; }
				
			}
			
			if ((n + l >= 0) && (n + l <= 15)){ 
			
				y = n + l; 
			
			} else{
				
				if (n + l < 0){ y = 15; }
				if (n + l > 15){ y = 0; }
				
			}	

			if (explTorus.P[0][x][y] == 1){ q = q + 1; }
			
		}
		
	}
	
	if (explTorus.P[0][m][n] == 1){ q = q - 1; }
	
	return q;
	
}

function RND100(){

	min = Math.ceil(0);
	max = Math.floor(100);
	
	return Math.floor(Math.random() * (max - min + 1)) + min; // max & min both included 

}

function MatrixToString(Q,p){
	
	B = ""; // binary string
	shaB = ""; // hash of binary string
	
	for (i = 0; i < Q.length; i++){
		
		for (j = 0; j < Q[0].length; j++){
			
			B = B + Q[i][j];
			
		}
		
	}	
	
	shaB = sha256(B + publicSalt);

	if (p == "binary"){ return B; }
	if (p == "hash"){ return shaB; }
	
}

function clearCanvas(canvas){

	let context = canvas.getContext("2d");

    context.clearRect(0,0, canvas.width, canvas.height);
    context.fillStyle = '#040408';
    context.fillRect(0,0, canvas.width, canvas.height);

}

function populateNull(Q, s){

    for (let i = 0; i < s; i++){
        
    	let t = [];
        
        for (let j = 0; j < s; j++){
        	
        	t.push(null);
        	
        }
        	
        Q.push(t);
        
    }

}

function populateNullz(Q, s, n){

	for (let i = 0; i < n; i++){
	
		let u = [];

    	for (let j = 0; j < s; j++){
        
    		let v = [];
        
        	for (let k = 0; k < s; k++){
        	
        		v.push(null);
        	
        	}
        	
        	u.push(v);
        
    	}
    	
    	Q.push(u);
    	
    }

}

function deleteAtrbs(){

	// console.log("deleteAtrbs(): Hello.");

	document.getElementById("outpStep").innerHTML = "-";

	document.getElementById("originHash").innerHTML = "________________________________________________________________";
	document.getElementById("currentHash").innerHTML = "-";

	document.getElementById("outpIt").innerHTML = "-";
	document.getElementById("outpMin").innerHTML = "-";
	document.getElementById("outpMax").innerHTML = "-";
	document.getElementById("outpPeak").innerHTML = "-";
	document.getElementById("terminus").innerHTML = "-";

}

function deleteFrequencies(myTorus){

	for (let i = 0; i < 16; i++){ for (let j = 0; j < 16; j++){ myTorus.F[i][j] = 0; } }

}

function checkHash(H){

	// console.log("[auxiliary.js] checkHash(" + H + "): Hi!");

	let output = null;
	if (RND100() > 50){ ouput = false; } else { output = true; }
	return output;

}

function evaluateSGPI(){

	// console.log("[auxiliary.js] evaluateSGPI(): Hi!");

	let originHash = document.getElementById("originHash").textContent;
	let temp = checkHashForDate(originHash, "originHash");

	if (temp != null){

		let gen = document.getElementById("disp-iter").innerHTML;
		gen = parseInt(parseInt(gen) + 1);
		let peak = document.getElementById("disp-peak").innerHTML;

		let mySGPI = document.getElementById("SGPI");
		mySGPI.innerHTML = temp[0] + "." + gen + "." + peak + "." + temp[1];

	}

}

/**
* Checks if a given hash string contains today's date in 
* MMDD, YYMMDD, or YYYYMMDD formats.
* @param {string} hashString - The hex string to search.
* @returns {string} - null if no date match is found, [suite, index] if date match is found
* includes logic to detect PIE day
*/
function checkHashForDate(hashString, targetElement) {
	const now = new Date();
	
	// Extract date components
	const yearFull = now.getFullYear().toString();
	const month = (now.getMonth() + 1).toString().padStart(2, '0');
	const day = now.getDate().toString().padStart(2, '0');

	const targets = [];
	const cleanHash = hashString.toLowerCase();

	// Check if tomorrow is Pi Day (March 14th)
	const isPiDay = (month === "03" && day === "14");

	if (isPiDay) {
		// Pi Day Targets
		if (suite4) targets.push("3141");      // First 4 digits
		if (suite6) targets.push("314159");    // First 6 digits
		if (suite8) targets.push("31415926");  // First 8 digits
	} else {
		// Regular Date Targets
		const yearShort = yearFull.slice(-2);
		if (suite4) targets.push(month + day);
		if (suite6) targets.push(yearShort + month + day);
		if (suite8) targets.push(yearFull + month + day);
	}

	let output = null;

	for (const target of targets) {
		const index = cleanHash.indexOf(target);
		if (index !== -1) {
			const before = hashString.substring(0, index);
			const match = hashString.substring(index, index + target.length);
			const after = hashString.substring(index + target.length);

			output = [target.length, index];
			
			let highlightedString = `${before}<span style="color:#ffffff">${match}</span>${after}`;
			document.getElementById(targetElement).innerHTML = highlightedString;
			break; // Stop at the first/longest match found
		}
	}        

	return output;
}