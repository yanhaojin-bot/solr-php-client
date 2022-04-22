<?php
ini_set("memory_limit", -1);
include 'SpellCorrector.php';

// make sure browsers see this page as utf-8 encoded HTML
//header('Content-Type: text/html; charset=utf-8');
$limit = 10;
$query = isset($_REQUEST['q']) ? $_REQUEST['q'] : false;
$results = false;
$path2url = array_column(array_map('str_getcsv', file('/Users/yanhaojin/Downloads/LATIMES/URLtoHTML_latimes_news.csv')), 1, 0);
$rankAlgo = "Lucene";

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
            'sort' => 'pageRankFile desc'

        );
        if ($_GET['rankAlgo'] == "Lucene") {
            $results = $solr->search($query, 0, $limit);
        } else if ($_GET['rankAlgo'] == "PageRank") {
            $results = $solr->search($query, 0, $limit, $additionalParameters);
        }
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
    <style>
        #suggestion-list {
            float: left;
            list-style: none;
            margin-top: -3px;
            padding: 0;
            width: 190px;
            position: absolute;
        }

        #suggestion-list li {
            padding: 10px;
            background: #f0f0f0;
            border-bottom: #bbb9b9 1px solid;
        }

        #suggestion-list li:hover {
            background: #ece3d2;
            cursor: pointer;
        }

        #search-box {
            padding: 10px;
            border: #a8d4b1 1px solid;
            border-radius: 4px;
        }
    </style>

    <script src="https://code.jquery.com/jquery-2.1.1.min.js" type="text/javascript"></script>
    <script>
        $(document).ready(function() {
            $("#search-box").keyup(function() {
                $.ajax({
                    type: "GET",
                    url: "suggest.php",
                    data: 'q=' + $(this).val(),
                    beforeSend: function() {
                        
                    },
                    success: function(data) {
                        console.log(data);
                        $("#suggesstion-box").show();
                        $("#suggesstion-box").html(data);
                        $("#search-box").css("background", "#FFF");
                    }
                });
            });
        });

        function selectCountry(val) {
            $("#search-box").val(val);
            $("#suggesstion-box").hide();
        }
    </script>
    <form accept-charset="utf-8" method="get">
        <div class="frmSearch">
            <!-- <input type="text" id="search-box" name="q" placeholder="Country Name" /> -->
            <!-- <div id="suggesstion-box"></div> -->

            <label for="q">Search:</label>
            <!-- <input id="search-box" name="q" type="text" value="<?php echo htmlspecialchars($query, ENT_QUOTES, 'utf-8'); ?>" /> -->
            <input type="text" id="search-box" name="q" placeholder="keyword" />
            <div id="suggesstion-box"></div>
            <label for="pageRank"> Rank algorithm: </label>
            <input type="radio" name="rankAlgo" value="Lucene" <?php if (isset($_REQUEST['rankAlgo']) && $_REQUEST['rankAlgo'] == 'Lucene') {
                                                                    echo 'checked="checked"';
                                                                } ?>>Lucene
            <input type="radio" name="rankAlgo" value="PageRank" <?php if (isset($_REQUEST['rankAlgo']) && $_REQUEST['rankAlgo'] == 'PageRank') {
                                                                        echo 'checked="checked"';
                                                                    } ?>>PageRank
            <input type="submit">
        </div>
    </form>
    <?php


    if ($query) {
        // display correction
        $correct_term = SpellCorrector::correct($query);
        if (strcmp($correct_term, $query) != 0) {
            $link_address = "http://localhost:9000/?q=". $correct_term . "&rankAlgo=". $_REQUEST['rankAlgo'];
            echo "Did you mean <a href='$link_address'> $correct_term </a>";
        } 
    }


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
                            <?php echo "Title : <a href = $url> $title;</a></br>" ?>
                            <?php echo "URL : <a href = $url> $url;</a></br>" ?>
                            <?php echo "ID : $id;</br>" ?>
                            <?php echo "Desc : $desc</br>" ?>
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
    ?>  <?php
                                        }
                                            ?>
</body>

</html>