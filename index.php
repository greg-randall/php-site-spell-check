<?php
include 'dict.php'; // array called $dictionary. this file of words is from here: http://norvig.com/google-books-common-words.txt . read a little about the methodology of that list http://norvig.com/mayzner.html 

//add custom dictonary option. add one per line of a text file called 'custom-dict.txt'
$custom=explode(PHP_EOL,file_get_contents('custom-dict.txt'));//get the custom dictonary, and explode line by line
foreach ($custom as &$cword){ //loop through all lines
	$custom_dict[clean_word($cword)]=1; //add words to the custom dictonary after cleaning them to the same standard as the big dictonary. 
}



$spell_check_base_url="http://localhost:8888/spell-check/index.php?p="; //base url that you access the spell chececker from ie the directory that you have uploaded it to

$url=$_GET["p"]; // gets the page passed through the url 

$page = file_get_contents ($url); //gets the requested url

$script=false; //remeber's if we're in a seciton of script or style and ignores it

//this is a little bit of a goofy way to do this but it works
//basically we want all the text on their own lines and all the html stuff on their own lines
//with one html item per line
$page=preg_replace( "/\r|\n/", "", $page);//get rid of all the line breaks in the page
$page=preg_replace( "/\s+/", " ", $page);//replace many linebreaks/spaces/etc with one space
$page=str_replace(array(">","<"),array(">\n","\n<"),$page);//make sure there's a linebreak before and after all html 
$page=str_replace("\n ","\n",$page);//get rid of leading spaces on lines

$lines=explode("\n",$page);//split the page up into seperate lines

foreach ($lines as &$line) {//go through each line one at a time
	
	$line=preg_replace('#(href|src)="([^:"]*)(?:")#',"$1=\"$url/$2\"",$line); //make all relataive links absolute
	
	
	//the next two if statments ignore stuff between style or script tags
	if(substr($line,0,7)=="<script"|substr($line,0,6)=="<style"){
		$script=true;
	}
	if(substr($line,0,9)=="</script>"|substr($line,0,8)=="</style>"){
		$script=false;
	}
	
	//if a line doesnt start with a less than it's a line of text, also make sure we're not in a script
	if($line[0]!="<"&&substr($line,0,3)!="-->"&&$script==false){
		$words=explode(" ",$line);//spit the line into an array of words
		foreach ($words as &$word) {//look at each word in turn
			if(isset($dict[clean_word($word)])|isset($custom_dict[clean_word($word)])|clean_word($word)==""|clean_word($word)=="NBSP"){//see if the word is contained in the dictonary and make sure the word isn't blank and that it's not a nonbreaking space (we need to clean the word before we can see if it's in the dictonary)
				echo "$word ";//if it's in the dictonary just print the word out
			}
			else{//if it's not in the dictonary make the word red
				echo "\n<span style='background-color:red;' note='".clean_word($word)."'>$word</span> \n";
				$misspelled[]=$word;//record the list of words that aren't speled right
			}
		}
		
	}else if(trim($line)=="</body>"){// find the end of the page and print the list of misspelled words
		$misspelled=array_unique($misspelled);//gets rid of duplicates
		echo "<h3>Misspelled Words:</h3>\n<p>";
		foreach($misspelled as &$mword){
			echo "$mword, ";
		}
		echo "</p>\n</body>";	
	}else if(substr($line,0,3)=="<a "){//if a line contains a link rework it to always stay inside the spellchecker
 		$line = str_replace('href="','href="'.$spell_check_base_url,$line);//add this page's url to make you stay inside the link checker as you click around the page
 		echo "\n".$line."\n";
  	}else{//if it's a line that's not a text line just print it 
		echo "\n".$line."\n";
	}

}





function clean_word($word){//the dictonary needs a cleaning step
	
	$clean_word=strtoupper($word); //make string uppercase
	$clean_word=trim($clean_word); //remove leading/trailing whitespace
	if(!ctype_alnum(substr($clean_word,-2,1))&&substr($clean_word,-1,1)=="S"){//if word has apostophie and a 's' (ie your's) drop those charecters
		$clean_word=substr($clean_word,0,-2);//remove apostropie and the 's'
	}
	$clean_word=html_entity_decode($clean_word); //decode the html encoded stuff to regular letters (ie &nbsp; to " ")
	$clean_word=preg_replace("/[^\w]/", '', $clean_word); //remove all non letters
	$clean_word=preg_replace("/[0-9]+/", '', $clean_word); //remove all non letters
	return($clean_word);
}
?>