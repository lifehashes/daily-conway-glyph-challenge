function New(myType, threshold, sourceBin){

    // console.log("New() initiated for type " + myType);
    
    // Torus constructor(canv, tres, shap, back, outp)
    
    if ((myType == "Explorer") && (explTorus == null)){
        
        explTorus = new Torus("explCanvas", threshold, "circle", "regular", 16, true, sourceBin);
		let originHash = MatrixToString(explTorus.O,"hash");
        document.getElementById("originHash").innerHTML = originHash;
		// document.getElementById("originHash").innerHTML = document.getElementById("seekHash").innerHTML;
		checkHashForDate(originHash, "originHash");          	

    	document.getElementById("ctrlRewind").disabled = true;
    	document.getElementById("ctrlPause").disabled = true;
    	document.getElementById("ctrlForw").disabled = false;
    	document.getElementById("ctrlPlay").disabled = false;
    
    }
    
    if ((myType == "Seeker") && (seekTorus == null)){
    
    	seekTorus = new Torus("seekCanvas", threshold, "circle", "regular", 16, false, sourceBin);
    	document.getElementById("seekHash").innerHTML = MatrixToString(seekTorus.O,"hash");
    
    }
    
    if ((myType == "SeekerMobile") && (seekTorus == null)){
    
    	seekTorus = new Torus("seekCanvas", threshold, "circle", "regular", 32, false, sourceBin);
    	document.getElementById("seekHash").innerHTML = MatrixToString(seekTorus.O,"hash");
    
    }
    
    if ((myType == "Signature") && (signTorus == null)){
    
    	signTorus = new Torus("signCanvas", threshold, "square", "regular", 12, false, sourceBin);
    
    }
    
    if ((myType == "Profile") && (profTorus == null)){
    
    	profTorus = new Torus("profCanvas", threshold, "square", "inverted", 12, false, sourceBin);
    
    }

}

function Submit(){

    // console.log("Submit(): Hello.");
    document.getElementById("ctrlRewind").disabled = true;
    document.getElementById("ctrlPause").disabled = true;
    document.getElementById("ctrlForw").disabled = false;
    document.getElementById("ctrlPlay").disabled = false;

    if (explTorus == null){ 
        
        let sourceBinary = document.getElementById("startBin").value;
        // console.log("Submit(): Binary string provided has length " + sourceBinary.length + ".");
        New("Explorer", null, sourceBinary);
    
    } else{

        // console.log("Submit(): ERROR! There already exists a configuration on the Explorer. Please discard before loading.");

    }

}

function Load(){

    // console.log("Load(): Hello.");

    document.getElementById("ctrlRewind").disabled = true;
    document.getElementById("ctrlPause").disabled = true;
    document.getElementById("ctrlForw").disabled = false;
    document.getElementById("ctrlPlay").disabled = false;    

}

function Discard(myType){

    // console.log("Discard() initiated for type " + myType);
    
    if ((myType == "Explorer") && (explTorus != null)){
    
    	Pause();
    	clearCanvas(document.getElementById("explCanvas"));
    	explTorus = null;

    	deleteAtrbs();

    	document.getElementById("ctrlRewind").disabled = true;
    	document.getElementById("ctrlPause").disabled = true;
    	document.getElementById("ctrlForw").disabled = true;
    	document.getElementById("ctrlPlay").disabled = true;

    	document.getElementById("startBin").value = "";

		document.getElementById("SGPI").textContent = "-- S.G.P.I. --";

		document.getElementById("disp-iter").textContent = 0;
		document.getElementById("disp-peak").textContent = 0;
		document.getElementById("disp-min").textContent = 0;
		document.getElementById("disp-max").textContent = 0;

		document.getElementById("disp-hash").textContent = "----";
		document.getElementById("disp-index").textContent = "--";

		const btn = document.getElementById("btn-submit-stage");
		if (btn) {
			btn.textContent = "Submit";
		}
    
    }
    
    if ((myType == "Seeker") && (seekTorus != null)){
    
    	clearCanvas(document.getElementById("seekCanvas"));
    	seekTorus = null;
    	document.getElementById("seekHash").innerHTML = "________________________________________________________________";
    	// console.log("Discard(): Seeker discarded successfully.");
    
    }
    
    if ((myType == "Profile") && (profTorus != null)){
    
    	clearCanvas(document.getElementById("profCanvas"));
    	profTorus = null;
    	// console.log("Discard(): Profile discarded successfully.");
    
    }
    
    if ((myType == "Signature") && (signTorus != null)){
    
    	clearCanvas(document.getElementById("signCanvas"));
    	signTorus = null;
    	// console.log("Discard(): Signature discarded successfully.");
    
    }

}

function Pause(){

    // console.log("Pause(): Hello.");

    document.getElementById("ctrlPause").disabled = true;
    document.getElementById("ctrlForw").disabled = false;
    document.getElementById("ctrlPlay").disabled = false;

    explTorus.goFlag = false;

}

function Restart(){

	// console.log("Restart(): Hello.");

	let sourceBinary = MatrixToString(explTorus.O, "binary");
	Discard("Explorer");
	New("Explorer", null, sourceBinary);

}

function Iterate(parameter){

    // console.log("Iterate(" + parameter + "): Hello.");
    document.getElementById("ctrlRewind").disabled = false;

    if (parameter == 1){

        document.getElementById("ctrlPause").disabled = true;
        document.getElementById("ctrlForw").disabled = false;
        document.getElementById("ctrlPlay").disabled = false;  
        
        window.requestAnimationFrame(() => explTorus.Loop());

    }

    if (parameter == 0){

        document.getElementById("ctrlPause").disabled = false;
        document.getElementById("ctrlForw").disabled = true;
        document.getElementById("ctrlPlay").disabled = true;

        explTorus.goFlag = true;
        window.requestAnimationFrame(() => explTorus.Loop());

    }

}

function Transfer(Source, Target){

	// console.log("Transfer(" + Source + ", " + Target +  ")");

	let sourceBinary = null;

	if ((Source == "Seeker") && (seekTorus != null)){ sourceBinary = MatrixToString(seekTorus.O, "binary"); }
	if ((Source == "Profile") && (profTorus != null)){ sourceBinary = MatrixToString(profTorus.O, "binary"); }
	if ((Source == "Signature") && (signTorus != null)){ sourceBinary = MatrixToString(signTorus.O, "binary"); }
	
	if (sourceBinary != null){
	
		if ((Target == "Explorer") && (explTorus == null)){
		
			New("Explorer", null, sourceBinary);
		
		}
		
		if ((Target == "Profile") && (profTorus == null)){
		
			New("Profile", null, sourceBinary);
		
		}
		
		if ((Target == "Signature") && (signTorus == null)){
		
			New("Signature", null, sourceBinary);
		
		}
	
	} else { console.log("Transfer(): Could not find a viable source binary."); }

}

function SeekStart(){

	// console.log("Seek(): Hello.");
	if (seekerFlag == false){ seekerFlag = true; }
	seekCounter = 0;
	Seek();

}

function Seek(){
		
	// console.log("Seek(): Hi!");

	if (seekerFlag == true){
	
		seekCounter = seekCounter + 1;
		document.getElementById("seekAttempt").innerHTML = seekCounter;

		Discard('Seeker');
		New('Seeker', RND100());
		
		let currentSeekHash = document.getElementById("seekHash").innerHTML;
		let compareFlag = checkHashForDate(currentSeekHash, "seekHash");		
		if (compareFlag != null){ 
			
			seekerFlag = false; 
			document.getElementById("disp-hash").textContent = document.getElementById("seekHash").textContent.substring(0, 16) + "...";
			document.getElementById("disp-index").textContent = compareFlag[1];

			if (autoHashActive){
				nextStage(4);
				Transfer('Seeker', 'Explorer');
				Iterate(0);
			}
		
		}

		setTimeout(() => { window.requestAnimationFrame(() => Seek()); }, 0);

	}

}

function SeekStop(){

	// console.log("SeekStop(): Hello.");
	if (seekerFlag == true){ seekerFlag = false; }

}