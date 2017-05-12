<?php
include 'dict.php'; // array called $dictionary.

$test="spellerd this is spee'lt";

$words=explode(" ",$test);

foreach ($words as &$word) {
		if(isset($dict[clean_word($word)])){
			echo "$word ";
		}else
		{
			echo "<span style='background-color:red;'>$word</span> ";
		}
}


function clean_word($word){
	$clean_word=preg_replace("/[^\w]/", '', $word); //remove all non letters
	$clean_word=strtoupper($clean_word); //make string uppercase
	$clean_word=trim($clean_word);
	return($clean_word);
}
?>