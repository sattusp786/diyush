<?php
  
  require_once('config.php');

  function delTree($dir) {

    echo $dir . "<br>";

    if (is_dir($dir)) {
       
      $objects = scandir($dir);
      foreach ($objects as $object) {
        if ($object != "." && $object != "..") {
          if (filetype($dir."/".$object) == "dir") 
             delTree($dir."/".$object); 
          else unlink   ($dir."/".$object);

          echo $dir."/".$object . "<br>";

        }
      }
      reset($objects);

      if($dir != DIR_CACHE)
        rmdir($dir);
    }
 }


echo DIR_CACHE . "<br>";

delTree(DIR_CACHE);

 echo "cache folder deleted!";

//delTree(DIR_MODIFICATION);
//echo "modification folder deleted!";

?>