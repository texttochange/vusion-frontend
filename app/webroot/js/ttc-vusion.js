define([], function() {

	var vusion = vusion || {};
 
	// create a general purpose namespace method
	// this will allow us to create namespace a bit easier
	vusion.createNS = function (namespace) {
    	var nsparts = namespace.split(".");
    	var parent = vusion;
 
	    // we want to be able to include or exclude the root namespace 
    	// So we strip it if it's in the namespace
    	if (nsparts[0] === "vusion") {
        	nsparts = nsparts.slice(1);
    	}
 
	    // loop through the parts and create 
    	// a nested namespace if necessary
		for (var i = 0; i < nsparts.length; i++) {
		    var partname = nsparts[i];
		    // check if the current parent already has 
		    // the namespace declared, if not create it
		    if (typeof parent[partname] === "undefined") {
		        parent[partname] = {};
		    }
		    // get a reference to the deepest element 
		    // in the hierarchy so far
		    parent = parent[partname];
		}
		// the parent is now completely constructed 
		// with empty namespaces and can be used.
		return parent;
	};

	vusion.createNS('vusion.data');
	vusion.getData = function () {
		return vusion.data;
	}
	vusion.setData = function(data) {
		vusion.data = data;
	}

	//attache our vusion object to the window
	window.vusion = vusion;
	return vusion;
});