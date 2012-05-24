<?php

include("lib/words.package.php");
include("modules/magpierss-0.72/rss_fetch.inc");

// RSS http://news.google.com/news?pz=1&cf=all&ned=us&hl=en&output=rss

// make up bible shit:
/*
$words = new Words();
$words->parseFile("source/kjb.txt");
echo $words->getSentence() . "\n";
*/

// make up stp lyrics:
/*
$words = new Words();
$words->parseFile("source/stp-dead_and_bloated.txt");
$words->parseFile("source/stp-sex_type_thing.txt");
$words->parseFile("source/stp-wicked_garden.txt");
$words->parseFile("source/stp-sin.txt");
$words->parseFile("source/stp-naked_sunday.txt");
$words->parseFile("source/stp-creep.txt");
echo $words->getSentence() . "\n";
*/

$feeds = array(
	"http://news.google.com/news?pz=1&cf=all&ned=us&hl=en&output=rss",
	"http://www.npr.org/rss/rss.php?id=1001",
	"http://www.nytimes.com/services/xml/rss/nyt/HomePage.xml",
	"http://news.yahoo.com/rss/;_ylt=Ak7a3aXFZMdG7yj27NMuNWy5scB_;_ylu=X3oDMTFnMnR2bGMwBG1pdANSU1MgU2l0ZUluZGV4IFVTBHBvcwMzBHNlYwNNZWRpYVJTU0VkaXRvcmlhbA--;_ylg=X3oDMTFlamZvM2ZlBGludGwDdXMEbGFuZwNlbi11cwRwc3RhaWQDBHBzdGNhdAMEcHQDc2VjdGlvbnM-;_ylv=3",
	"http://feeds.nbcnewyork.com/nbcnewyork/news/top-stories"
);

$headline_words = new Words();
$body_words = new Words();
// $authors = new Words();

foreach($feeds as $cur_feed) {

	$rss = @fetch_rss($cur_feed);

	foreach($rss->items as $k => $item) {
	
		$head = strip_tags($item["title"]);
		// $body = strip_tags($item["description"]);
		$body = strip_tags($item["summary"]);

		$headline_words->parseString( $head );
		$body_words->parseString( $body );
		// $authors->parseString();

	}

}

$new_articles = array();

for ($i = 0; $i < 10; $i++) {

	$article = new stdClass();
	$article->headline = $headline_words->getSentence(10,30);
	$article->sentences = array();

	for ($j = 0; $j < 10; $j++)
		$article->sentences[] = $body_words->getSentence(10,30);
		
	$article->body = implode(" ", $article->sentences);

	$new_articles[] = $article;

}

?>
<!DOCTYPE html>
<html>
<head>

</head>
<body>

<?php foreach ($new_articles as $article) { ?>
	<h2><?php echo $article->headline; ?></h2>
	<p><?php echo $article->body; ?></p>

<?php } ?>

</body>
</html>