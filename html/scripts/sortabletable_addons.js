// Thanks to Brian K. Cantwell for the initial code

function usCurrencyConverter( s )
{
	var n = s;
	var i = s.indexOf( "$" );
	if ( i == -1 )
		i = s.indexOf( "," );
	if ( i != -1 )
	{
		var p1 = s.substr( 0, i );
		var p2 = s.substr( i + 1, s.length );
		return usCurrencyConverter( p1 + p2 );
	}

	return parseFloat( n );
}

// Thanks to Bernhard Wagner for submitting this function

function replace8a8(str) {
	str = str.toUpperCase();
	var splitstr = "____";
	var ar = str.replace(
		/(([0-9]*\.)?[0-9]+([eE][-+]?[0-9]+)?)(.*)/,
	 "$1"+splitstr+"$4").split(splitstr);
	var num = Number(ar[0]).valueOf();
	var ml = ar[1].replace(/\s*([KMGB])\s*/, "$1");

	if (ml == "K")
		num *= 1024;
	else if(ml == "M")
		num *= 1024 * 1024;
	else if (ml == "G")
		num *= 1024 * 1024 * 1024;
	else if (ml == "T")
		num *= 1024 * 1024 * 1024 * 1024;
	// B and no prefix

	return num;
}

SortableTable.prototype.addSortType( "UsCurrency", usCurrencyConverter ); 
SortableTable.prototype.addSortType( "NumberK", replace8a8 );
