// Bucket JS
// HTML5 localstorage library
// Tushar Singh 2013

_bucket = function(str){ return _bucket.construct(str); };

_bucket.suffix  = "bucket__";
_bucket.sep     = "__";

_bucket.construct = function(str){
    
    var
    bSuffix = _bucket.suffix,
    bSep    = _bucket.sep,
    
    bucket = function(){
        if (!str) {
            this.bucketName = '';
            bSep            = '';
        }
        else {
            this.bucketName = str;
        }
    };
    
    bucket.prototype = {
        
        set: function(key,value){
            var val = value;
            
            if (typeof value=="object") {
                val = JSON.stringify(value);
            }
            
            localStorage[bSuffix+this.bucketName+bSep+key] = val;
            
        },
        
        get: function(key){
            if (key) {
                return _bucket.parse (localStorage[bSuffix+this.bucketName+bSep+key]);
            }
            else {
                return _bucket.get(this.bucketName);
            }
        },
        
        remove: function(key){
            if (key) {
                localStorage.removeItem(bSuffix+this.bucketName+bSep+key);
            }
            else {
                _bucket.remove(this.bucketName);
            }
        }
        
    }
    
    return new bucket();

}

_bucket.parse = function(string){
    try {
        //check for JSON
        JSON.parse(string)
    } catch(e) {
        
        if (string!=null && string!=undefined) {
            //check for boolean
            if (string=="true") { return true } else if (string=="false") { return false }
            
            //check for number
            else if (/^[0-9]+.[0-9]+$/.test(string)) {
                return Number(string)
            }
            
            //scheck for null/undefined
            else if (string=="null") { return null } else if (string=="undefined") { return undefined }
            
        }
        
        return string
        
    }
    return JSON.parse(string)
}

_bucket.get = function(bucketName){
    
    var value = {};
    
    if (!bucketName) {
        for (var key in localStorage) {
            if (key.indexOf(_bucket.suffix) == 0) {
                
                //get bucketName via split()
                //bucket names mustn't contain _bucket.suffix for this reason
                var _bucketName = key.replace(_bucket.suffix,'').split(_bucket.sep)[0];
                
                if (!value[_bucketName]) {
                    //create sub-object if it doesn't already exist
                    value[_bucketName] = {}
                }
                value[_bucketName][key.replace(_bucket.suffix+_bucketName+_bucket.sep,'')] = _bucket.parse(localStorage[key]);
            }
            
        }
    }
    else {
        for (var key in localStorage) {
            
            if (key.indexOf(_bucket.suffix+bucketName) == 0) {
                value[key.replace(_bucket.suffix+bucketName+_bucket.sep,'')] = _bucket.parse(localStorage[key]);
            }
            
        }
    }
    if (JSON.stringify(value).length == 2) {
        return undefined;
    }
    
    return value;
}

_bucket.remove = function(bucketName){
    
    if (!bucketName) {
        bucketName = '';
    }
    
    for (var key in localStorage) {
        
        //check if key begins with bucket suffix
        //if bucketName is empty, all buckets will be removed
        if (key.indexOf(_bucket.suffix+bucketName) == 0) {
            localStorage.removeItem(key);
        }
            
    }
    
}

//shorthand
if (typeof _b == "undefined") {
    _b = _bucket;
}