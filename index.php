<?php

    if(!isset($_GET["p"])|$_GET["p"]==""){ //check to see if the url is set. if not set prompts for a url. 
        echo "Check URL:</h3>\n<form action=\"index.php\" method=\"get\"><input type=\"text\" name=\"p\" value=\"http://\" size=\"100\" style=\"margin-bottom:10px;\"><br><input type=\"submit\"></form>";
        exit();
    }

    $spell_check_base_url = strtok('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], '?') . "?p="; //base url. gets the current page's url. strips off the url's parameters and then adds back the blank prameter we want


    include 'dict.php'; // array called $dictionary. this file of words is from here: http://norvig.com/google-books-common-words.txt . read a little about the methodology of that list http://norvig.com/mayzner.html
    $custom = explode(PHP_EOL, file_get_contents('custom-dict.txt')); //get the custom dictonary and explode line by line. add one word per line of a text file called 'custom-dict.txt'

    foreach($custom as & $cword) { //loop through all lines
       $custom_dict[clean_word($cword) ] = 1; //add words to the custom dictonary after cleaning them to the same standard as the big dictonary.
    }


    $url = $_GET["p"]; // gets the page passed through the url
    $page = file_get_contents($url); //gets the requested url
    $script = false; //remebers if we're in a seciton of script or style and ignores it


    // this is a little bit of a goofy way to do this but it works
    // basically we want all the text on their own lines and all the html stuff on their own lines
    $page = preg_replace("/\r|\n/", "", $page); //get rid of all the line breaks in the page
    $page = preg_replace("/\s+/", " ", $page); //replace many linebreaks/spaces/etc with one space
    $page = str_replace(array(">","<") , array(">\n","\n<") , $page); //add a linebreak before and after all html elements
    $page = str_replace("\n ", "\n", $page); //get rid of leading spaces on lines
    $lines = explode("\n", $page); //split the page up into seperate lines



    foreach($lines as & $line) { //go through each line one at a time
       $line = preg_replace('#(href|src)="([^:"]*)(?:")#', "$1=\"$url/$2\"", $line); //make all relataive links absolute. we have to do this becaues we're displaying someone else's site on our webserver which breaks things like href="asdf/asdf.css"

       // the next two if statments ignore stuff between style or script tags
       if (substr($line, 0, 7) == "<script" | substr($line, 0, 6) == "<style") {
          $script = true;
       }
       if (substr($line, 0, 9) == "</script>" | substr($line, 0, 8) == "</style>") {
          $script = false;
       }

        
       
       if ($line[0] != "<" && substr($line, 0, 3) != "-->" && $script == false) {// if a line doesnt start with a less than it's a line of text, also make sure we're not in a script
          $line = str_replace(array("-","–"), " - ", $line); //dictonary doesn't contain hyphenated (or emdash) things like "world-wide" adds a space so that the two words get spell checked seperately.
          $words = preg_split("/[\s ]+/", $line); //spit the line into an array of words
          foreach($words as & $word) { //look at each word in turn
             if (isset($dict[clean_word($word)]) | isset($custom_dict[clean_word($word)]) | clean_word($word) == "" | clean_word($word) == "NBSP") { //see if the word is contained in the dictonary and make sure the word isn't blank and that it's not a nonbreaking space (we need to clean the word before we can see if it's in the dictonary)
                echo "$word "; //if it's in the dictonary just print the word out
             }
             else { //if it's not in the dictonary make the word red
                echo "\n<span style='background-color:red;' word-note='" . clean_word($word) . "' line-note='" . $line . "'>$word</span> \n"; //add some notes about the word and the line for debugging
                $misspelled[] = $word; //record the list of words that aren't speled right
             }
          }
       }
       else if (trim($line) == "</body>") { // find the end of the page and print the list of misspelled words, a form to change the page's url, and a link go to the live site
          $misspelled = array_values(array_unique($misspelled)); //gets rid of duplicates
          echo "<div style=\"margin-left:25px; background-color:white;\"><h3 style=\"color:black;\">Misspelled Words:</h3>\n<p style=\"color:black;\">";//print misspelled word heading
          foreach($misspelled as & $mword) {//list all misspelled words
             echo $mword . ', ';
          }
          echo "</p>\n<h3 style=\"color:black;\">Change URL:</h3>\n<form action=\"index.php\" method=\"get\"><input type=\"text\" name=\"p\" value=\"$url\" size=\"100\" style=\"margin-bottom:10px;\"><br><input type=\"submit\"></form><br />\n<h3 style=\"color:black;\"><a href=\"$url\">Go To Live Site</a></h3></div>\n</body>"; //print the change url field, the live site link, and close the body tag
       }
       else if (substr($line, 0, 3) == "<a ") { //if the line contains a link rework it to always stay inside the spellchecker. ie <a href="http://asdf.com"> changes to <a href="spellcheckerurl?p=http://asdf.com">
          $line = str_replace('href="', 'href="' . $spell_check_base_url, $line); //add this page's url to make you stay inside the link checker as you click around the page
          echo "\n" . $line . "\n";
       }
       else { //if it's a line that's not a text line just print it.
          echo "\n" . $line . "\n";
       }
    }




    function clean_word($word)
    { //the dictonary needs a cleaning step
      //the words in the dictonary have no punctuation, and are all uppercase
      //ie "hello " goes to "HELLO"   "Taco's" goes to "TACO"  
       $clean_word = html_entity_decode($word); //decode the html encoded stuff to regular letters (ie &nbsp; to " ")
       $clean_word = strtoupper($clean_word); //make string uppercase
       $clean_word = trim($clean_word); //remove leading/trailing whitespace
       if (!ctype_alnum(substr($clean_word, -2, 1)) && substr($clean_word, -1, 1) == "S") { //if word has apostrophe and a 's' (ie your's) drop those charecters
          $clean_word = substr($clean_word, 0, -2); //remove apostropie and the 's'
       }

       $clean_word = preg_replace("/[^\w]/", '', $clean_word); //remove all non letters
       $clean_word = preg_replace("/[0-9]+/", '', $clean_word); //remove all non letters
       return ($clean_word);
    }

?>
