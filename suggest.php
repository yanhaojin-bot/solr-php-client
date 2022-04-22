<?php

require_once('Apache/Solr/Service.php');
$solr = new Apache_Solr_Service('localhost', 8983, '/solr/myexample');
$query = "";
$previous = "";
$final_query_term = "";
if(!empty($_GET["q"])) {
    $query = $_GET["q"];
    $pos = strrpos($query, " ");
    if ($pos === false) {
        $final_query_term = $query;
    } else {
        $final_query_term = substr($query, $pos + 1);
        $previous = substr($query, 0 , $pos)." ";
    }
}

$limit = 10;
$result = $solr->autoCompleteQuery($final_query_term);

if(!empty($result)) {
    $raw = $result->__get("suggest");
    $raw= json_decode( json_encode($raw), true);
    $res_arr = $raw["suggest"][$final_query_term]["suggestions"];
?>
    
    <ul id="suggestion-list">
    <?php
    foreach($res_arr as $term) {
    ?>
    <li onClick="selectCountry('<?php echo $previous . $term["term"]; ?>');"><?php echo  $previous . $term["term"]; ?></li>
    <?php } ?>
    </ul>
    <?php } ?>
