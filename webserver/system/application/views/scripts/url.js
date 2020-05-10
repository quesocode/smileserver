function CmbUrl ()
{
	base_url = false;
	if(arguments.length)
	{
		if(arguments[0] !== false)
		{
			base_url = arguments[0];
		}
		if(arguments[1] && arguments[1].location.href)
		{
			this.win = arguments[1];
		}
	}
	if(!this.win)
	{
		this.win = window;
	}
	if(base_url === false)
	{
		base_url = this.win.location.href;
	}
	this.original = rooturl = base_url;
	urlvars = '';
	anchor = '';
	this.query_index = qstn_mark = this.original.indexOf('?');
	this.hash_index = hash_mark = this.original.indexOf('#');
	if(qstn_mark > -1)
	{
		rooturl = this.original.slice(0, qstn_mark);
		if(hash_mark > -1)
		{
	  		urlvars = this.original.slice(qstn_mark + 1, hash_mark);
		}
		else
		{
	  		urlvars = this.original.slice(qstn_mark + 1);
		}
	}
	if(hash_mark > -1)
	{
		anchor = this.original.slice(hash_mark + 1);
	}
	if(qstn_mark > -1 && hash_mark < 0)
	{
		rooturl = this.original.slice(0, qstn_mark);
	}
	else if(qstn_mark > -1 && hash_mark > -1)
	{
		rooturl = this.original.slice(0, qstn_mark);
	}
	else if(qstn_mark < 0 && hash_mark > -1)
	{
		rooturl = this.original.slice(0, hash_mark);
	}
	
	this.url = {
		root: rooturl,
		vars: urlvars,
		anchor: anchor
	}
	this.root = rooturl;
	this.vars = urlvars;
	this.anchor = anchor;
	this.getUrlVars();
}
CmbUrl.prototype.get = function ()
{
	query = anchor = '';
	if(q = this.getUrlQueryStr())
	{
		query = '?' + q;
	}
	if(a = this.anchor)
	{
		anchor = a; 
	}
	return this.root + query + '#' + anchor;
}
// Read a page's GET URL variables and return them as an associative array.
CmbUrl.prototype.getUrlVars = function ()
{
    if(this.urlVars == undefined)
    {
      this.urlVars = [];
      if(this.url.vars != '')
      {
        var vars = [], hash;
        var hashes = this.url.vars.split('&');
        for(var i = 0; i < hashes.length; i++)
        {
          hash = hashes[i].split('=');
          if(hash[0])
          {
          	this.setUrlVar(hash[0], hash[1]);
          }
        }
      }
    }
    return this.urlVars;
}
CmbUrl.prototype.setUrlVar = function (index, value)
{
  if(this.urlVars == undefined)
  {
    this.urlVars = [];
  }
  if(this.urlVars[index] == undefined)
  {
    this.urlVars.push(index);
  }
  this.urlVars[index] = value;
  return this;
}

CmbUrl.prototype.getUrlQueryStr = function ()
{
    var arr = this.getUrlVars();
    var str = '';
    for(var i = 0; i < arr.length; i++) {
	   var index = arr[i];
	   var value = arr[index];
	   if(index && value)
	   {
	   	str += '&' + index + '=' + value;
	   }
    }
    return str.slice(1);
}

CmbUrl.prototype.setAnchor = function ()
{
    anchor = false;
	if(arguments.length)
	{
		anchor = arguments[0];
	}
	this.anchor = this.url.anchor = anchor;
	if(this.win)
	{
		this.win.location.href = this.get();
	}
	return this;
	
}
CmbUrl.prototype.isUrlVar = function (key)
{
	var arr = this.getUrlVars();
	if(arr[key] != undefined)
	{
		return true;
	}
	return false;
}
