class Torus{

	static size = 16; // this is the NUMBER of cells, not their size
	static delay = 50;

	constructor(canv, tres, shap, back, size, outp, binary){
	
		this.canvas = document.getElementById(canv);
		this.context = this.canvas.getContext("2d");
		
		this.threshold = tres;
		
		this.cellShape = shap; // cell shape: DOT or SQUARE
		this.cellSize = size;
		this.background = back; // background colouring: NORMAL or INVERTED
		this.outputFlag = outp;
		
        this.O = []; // this is where we save the original starting configuration to enable restarting
        populateNull(this.O, Torus.size);
        
        this.M = []; // primary array to save the current configuration of the Torus
        populateNull(this.M, Torus.size);
        
        this.P = []; // this is where we save the current state of M to retain it's past (this will become useful for fancy graphics)
        populateNullz(this.P, Torus.size, 6);
        
        this.F = []; // this is where we save the frequencies of M, i.e. how often the individual cells are alive
        populateNull(this.F, Torus.size);
        
        this.step = 0;
        this.iteration = 0;
        this.goFlag = false;
        this.terminalFlag = false;
        
        this.cells = null;
        
        this.hashes = []; // we are going to save the hashes of all steps here
        this.statistics = [999, 0, 0, "-"]; // here we collect statistics of the Torus, i.e. min, max, peak, terminal state
        
        this.bin = binary;
        
        if (this.bin == null){ this.init(this.threshold); } else { this.load(this.bin); }
        this.createGrid(this.cellShape, this.background);
        
        // console.log("Torus.constructor: this.outputFlag = " + this.outputFlag);
	
	}
	
	init(threshold){
	
		if ((threshold == null) || (threshold < 0) || (threshold > 100)){ threshold = 70; }
	
		for (let i = 0; i < Torus.size; i++){ 
	
			for (let j = 0; j < Torus.size; j++){ 
			
				if (RND100() >= threshold){ this.M[i][j] = 1; } else { this.M[i][j] = 0; }
				this.O[i][j] = this.M[i][j]; // copy starting configuration from M to O
		
			}
		
		}
	
	}
	
	load(bin){
	
    	if (bin.length == 256){
    
        	let q = -1;
                
        	for (let i = 0; i < 16; i++){
            
            	for (let j = 0; j < 16; j++){
                
                	q++;
                	this.M[i][j] = bin[q];
                	this.O[i][j] = bin[q];
                
            	}
            
        	}

    	} else {

        	console.log("Load(): ERROR! The binary string provided by you has length " + bin.length + " - expected is a binary string of exactly 256 characters.");

    	}		
	
	}
	
    createGrid(param, qaram){     
    
    	this.cells = [];   

        for (let j = 0; j < Torus.size; j++){

            for (let i = 0; i < Torus.size; i++){

                this.cells.push(new Cell(this.context, i, j, param, qaram, this.cellSize, this.M, this.P)); 
                // param(eter) sets shape and size of cells in the Cell constructor
                // qaram(eter) sets dead/alive colouring of cells
                
            }

        }
        
        clearCanvas(this.canvas);
        for (let i = 0; i < this.cells.length; i++){ this.cells[i].draw(); }
        
        this.checkHashDuplicate();
        if (this.terminalFlag == false){ this.calcStats(); }
        if (this.outputFlag == true){ this.outputStats(); }

    }
    
    checkHashDuplicate(){

        let currentHash = MatrixToString(this.M,"hash");
        if (this.outputFlag == true){ document.getElementById("currentHash").innerHTML = currentHash; }
        let delta = 0;

        for (let j = 0; j < this.hashes.length; j++){

            if (this.hashes[j] == currentHash){

                // console.log("Torus.checkHashDuplicate(): Found duplicate of current hash at step " + j + ", stopping iterations.");
                
                delta = this.iteration - j;
                if (delta == 1){ this.statistics[3] = "STATIC"; }
                if (delta == 2){ this.statistics[3] = "2-FLICKER"; }
                if (delta == 3){ this.statistics[3] = "3-FLICKER"; }
                if (delta == 64){ this.statistics[3] = "GLIDER"; }
                if (currentHash == "67f022195ee405142968ca1b53ae2513a8bab0404d70577785316fa95218e8ba"){ this.statistics[3] = "VOID"; }

				this.terminalFlag = true;
                Pause();
                evaluateSGPI();

                if (autoHashActive && this.outputFlag == true){ 
                    
                    window.mySaveLogic();

                    setTimeout(() =>{

                        Discard('Explorer');
                        Discard('Seeker');
                        startAutoWorkflow();

                    }, 500); // timeout to ensure UI updates and renderings finalize before submission 


                
                }

            }

        }

        this.hashes.push(currentHash);

    }

    calcStats(){

        let count = 0;

        for (let i = 0; i < Torus.size; i++){

            for (let j = 0; j < Torus.size; j++){

                if (this.M[i][j] == 1){ 
                    
                    count = count + 1; 
                    this.F[i][j] = this.F[i][j] + 1;
                    if (this.F[i][j] > this.statistics[2]){ this.statistics[2] = this.F[i][j]; }

                }

            }

        }

        if (count < this.statistics[0]){ this.statistics[0] = count; }
        if (count > this.statistics[1]){ this.statistics[1] = count; }

    }
    
    outputStats(){
    
    	// console.log("outputStats(): Hello.");
    
    	document.getElementById("outpMin").innerHTML = this.statistics[0];
    	document.getElementById("outpMax").innerHTML = this.statistics[1];
    	document.getElementById("outpPeak").innerHTML = this.statistics[2];
    	
    	document.getElementById("terminus").innerHTML = this.statistics[3];
    	
    	document.getElementById("outpStep").innerHTML = this.step;
    	document.getElementById("outpIt").innerHTML = this.iteration;

        document.getElementById("disp-iter").innerHTML = this.step;
        document.getElementById("disp-peak").innerHTML = this.statistics[2];
        document.getElementById("disp-min").innerHTML = this.statistics[0];
        document.getElementById("disp-max").innerHTML = this.statistics[1];
    
    }

    calcNextStep(){

        // copy P's current state to Q
        for (let i = 0; i < Torus.size; i++){ for (let j = 0; j < Torus.size; j++){ this.P[5][i][j] = this.P[4][i][j]; } }
        for (let i = 0; i < Torus.size; i++){ for (let j = 0; j < Torus.size; j++){ this.P[4][i][j] = this.P[3][i][j]; } }
        for (let i = 0; i < Torus.size; i++){ for (let j = 0; j < Torus.size; j++){ this.P[3][i][j] = this.P[2][i][j]; } }
        for (let i = 0; i < Torus.size; i++){ for (let j = 0; j < Torus.size; j++){ this.P[2][i][j] = this.P[1][i][j]; } }
        for (let i = 0; i < Torus.size; i++){ for (let j = 0; j < Torus.size; j++){ this.P[1][i][j] = this.P[0][i][j]; } }

        // copy M's current state to P
        for (let i = 0; i < Torus.size; i++){ for (let j = 0; j < Torus.size; j++){ this.P[0][i][j] = this.M[i][j]; } }
    
    
        let n = 0; // let this be the number of neighbouring cells
        for (let i = 0; i < Torus.size; i++){ for (let j = 0; j < Torus.size; j++){
    
            n = CalcNeighbours(i,j);
                    
            if ((this.M[i][j] == 1) && ((n < 2) || (n > 3))){ this.M[i][j] = 0; }
            if ((this.M[i][j] == 1) && ((n = 2) || (n = 3))){ this.M[i][j] = 1; }
            if ((this.M[i][j] == 0) && (n == 3)){ this.M[i][j] = 1; }        
    
        }}
    
    }
    
    Loop(){

		this.iteration = this.iteration + 1;
        if (this.terminalFlag == false){ this.step = this.iteration; }

        this.calcNextStep();        
        this.createGrid(this.cellShape, this.background);      

        // TRIGGER THE GRAPH UPDATE HERE
        if (typeof updateRarityGraph === "function") {
            updateRarityGraph(this.step);
        }        
        
        let myDelay = null;
        if (!autoHashActive){ myDelay = Torus.delay; } else { myDelay = 5; } 

        if (this.goFlag == true){ setTimeout(() => { window.requestAnimationFrame(() => this.Loop()); }, myDelay); }

    }

}

class Cell{

    constructor(ctx, x, y, p, q, gs, Y, Z){

		this.context = ctx;
		this.x = x;
		this.y = y;
		
        this.p = p; // parameter to determine size and geometry of the cells: CIRCLE or SQUARE
        this.q = q; // parameter to set cell colour to REGULAR (white/black) or INVERTED (black/white)
        
		this.alive = (Y[y][x] == 1); // this checks whether the current cell is alive
		
		this.pastOne = (Z[0][y][x] == 1); // this checks whether the cell was alive in the previous step
		this.pastTwo = (Z[1][y][x] == 1); // this checks whether the cell was alive two steps prior to the current step
		this.pastThree = (Z[2][y][x] == 1);
		this.pastFour = (Z[3][y][x] == 1);
		this.pastFive = (Z[4][y][x] == 1);
		this.pastSix = (Z[5][y][x] == 1);
		
        this.gridsize = gs;

        // console.log("Cell constructor(x = " + x + ", y = " + y + ", p = " + p);

    }
    
    draw(){

		if (this.q == "regular"){ 
	
			this.context.fillStyle = '#000000';
	
            /*
			if (this.pastSix){ this.context.fillStyle = '#E3F6F6'; } else { this.context.fillStyle = '#121212'; }
			if (this.pastFive){ this.context.fillStyle = '#B8D3EA'; }
			if (this.pastFour){ this.context.fillStyle = '#C8BDA6'; }
			if (this.pastThree){ this.context.fillStyle = '#F47B50'; }
			if (this.pastTwo){ this.context.fillStyle = '#C2C5C1'; }
			if (this.pastOne){ this.context.fillStyle = '#E6DACA'; }
            */

			if (this.alive){ this.context.fillStyle = '#FFFFFF'; }

		} 
		
		if (this.q == "inverted"){ 
		
			if (this.alive){ this.context.fillStyle = '#000000'; } else { this.context.fillStyle = '#ffffff'; }
			
		}

        if (this.p == "circle"){

            // Circular cells
            this.context.beginPath();
		    this.context.arc(this.x * this.gridsize + this.gridsize/2, this.y * this.gridsize + this.gridsize/2, this.gridsize / 2, 0, 2*Math.PI);
		    this.context.fill();

        } 
        
        if (this.p == "square"){

            // Square-shaped cells
            this.context.fillRect(this.x*this.gridsize, this.y*this.gridsize, this.gridsize-1, this.gridsize-1);

        }

    }

}