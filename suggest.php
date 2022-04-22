<?php

require_once('Apache/Solr/Service.php');
$solr = new Apache_Solr_Service('localhost', 8983, '/solr/myexample');
$query = "";
if(!empty($_GET["q"])) {
    $query = $_GET["q"];
}

$limit = 10;
$result = $solr->autoCompleteQuery($query);

if(!empty($result)) {
    $raw = $result->__get("suggest");
    $raw= json_decode( json_encode($raw), true);
    $res_arr = $raw["suggest"][$query]["suggestions"];
?>
    
    <ul id="suggestion-list">
    <?php
    foreach($res_arr as $term) {
    ?>
    <li onClick="selectCountry('<?php echo $term["term"]; ?>');"><?php echo $term["term"]; ?></li>
    <?php } ?>
    </ul>
    <?php } ?>
