<?php
// make sure browsers see this page as utf-8 encoded HTML
header('Content-Type: text/html; charset=utf-8');
$limit = 10;
$query = isset($_REQUEST['q']) ? $_REQUEST['q'] : false;
$results = false; 
$path2url = array_column(array_map('str_getcsv', file('/Users/yanhaojin/Downloads/LATIMES/URLtoHTML_latimes_news.csv')), 1, 0);


if ($query) {
    // Lucene's ranking algorithm (default)

    // The Apache Solr Client library should be on the include path
    // which is usually most easily accomplished by placing in the
    // same directory as this script ( . or current directory is a default
    // php include path entry in the php.ini)
    require_once('Apache/Solr/Service.php');
    // create a new solr service instance - host, port, and corename
    // path (all defaults in this example)
    $solr = new Apache_Solr_Service('localhost', 8983, '/solr/myexample');

    // // if magic quotes is enabled then stripslashes will be needed
    // if (get_magic_quotes_gpc() == 1) {
    //     $query = stripslashes($query);
    // }
    // in production code you'll always want to use a try /catch for any
    // possible exceptions emitted by searching (i.e. connection
    // problems or a query parsing error)
    try {
        $additionalParameters = array(
            'fq' => 'a filtering query',
            'facet' => 'true',
            // notice I use an array for a multi-valued parameter
            'facet.field' => array(
                'field_1',
                'field_2'
            )
        );
        $results = $solr->search($query, 0, $limit);
    } catch (Exception $e) {
        // in production you'd probably log or email this error to an admin
        // and then show a special message to the user but for this example
        // we're going to show the full exception
        die("<html><head><title>SEARCH EXCEPTION</title><body><pre>{$e->__toString()}</pre></body></html>");
    }
}
?>
<html>

<head>
    <title>PHP Solr Client Example</title>
</head>

<body>
    <form accept-charset="utf-8" method="get">
        <label for="q">Search:</label>
        <input id="q" name="q" type="text" value="<?php echo htmlspecialchars($query, ENT_QUOTES, 'utf-8'); ?>" />
        <input type="submit" name="submit" value="Submit">
    </form>
    <?php
    // display results
    if ($results) {
        $total = (int) $results->response->numFound;
        $start = min(1, $total);
        $end = min($limit, $total);
    ?>
        <div>Results <?php echo $start; ?> - <?php echo $end; ?> of <?php echo $total; ?>:</div>
        <ol>
            <?php
            // iterate result documents
            foreach ($results->response->docs as $doc) {
                $id = $doc->id;
                $desc = $doc->og_description;
                $url = $doc->og_url;
                $title = $doc->title;

                if (is_null($url)) {
                    $path_parts = pathinfo($id);
                    $url = $path2url[$path_parts['basename']];
                }

                if (is_null($desc)) {
                    $desc = 'N/A';
                }
            ?>
                <li>
                    <tablestyle="border: 1px solid black; text-align: left>
                            <tr>
                            <?php echo "Title : <a href = $url> $title;</a></br>"?>
			                <?php echo "URL : <a href = $url> $url;</a></br>"?>
                            <?php echo "ID : $id;</br>"?>
			                <?php echo "Desc : $desc</br>"?>
                                <!-- <th><?php echo htmlspecialchars($field, ENT_NOQUOTES, 'utf-8'); ?></th>
                                <td><?php echo htmlspecialchars($value, ENT_NOQUOTES, 'utf-8'); ?></td> -->
                            </tr>
                        <?php
                        //}
                        ?>
                        </table>
                </li>
            <?php
            }
            ?>
        </ol>
    <?php
    } else {
    ?> <h1> empty </h1> <?php
                                }
                                    ?>
</body>

</html>