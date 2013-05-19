<?php

include("lib/words.package.php");
include("modules/magpierss-0.72/rss_fetch.inc");

// make up bible shit:
/*
$words = new Words();
$words->parseFile("source/kjb.txt");
echo $words->getSentence() . "\n";
exit();
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
*/

$words = new Words();

// $words->parseFile("source/lorem-translated-english.txt");
// http://www.william-shakespeare.info/william-shakespeare-sonnets.htm
/*
$words->parseFile("source/sonnet-18.txt");
$words->parseFile("source/sonnet-27.txt");
$words->parseFile("source/sonnet-29.txt");
$words->parseFile("source/sonnet-116.txt");
$words->parseFile("source/sonnet-126.txt");
$words->parseFile("source/sonnet-130.txt");
*/

/*
$words->parseFile("source/kjb.txt");

for ($p = 0; $p < 10; $p++) {

	echo "<p>";
	for ($i = 0; $i < rand(10,20); $i++)
		echo $words->getSentence() . "  ";
	echo "</p>";

}

exit();
*/


$feeds = array(
	"http://www.nypost.com/rss/newscore.xml",
	"http://news.google.com/news?pz=1&cf=all&ned=us&hl=en&output=rss",
	"http://www.npr.org/rss/rss.php?id=1001",
	"http://www.nytimes.com/services/xml/rss/nyt/HomePage.xml",
	"http://news.yahoo.com/rss/;_ylt=Ak7a3aXFZMdG7yj27NMuNWy5scB_;_ylu=X3oDMTFnMnR2bGMwBG1pdANSU1MgU2l0ZUluZGV4IFVTBHBvcwMzBHNlYwNNZWRpYVJTU0VkaXRvcmlhbA--;_ylg=X3oDMTFlamZvM2ZlBGludGwDdXMEbGFuZwNlbi11cwRwc3RhaWQDBHBzdGNhdAMEcHQDc2VjdGlvbnM-;_ylv=3",
	"http://feeds.nbcnewyork.com/nbcnewyork/news/top-stories",
	"http://rt.com/rss/",
	"http://feeds.bbci.co.uk/news/world/middle_east/rss.xml"
);

$page_title = new Words();
$headline_words = new Words();
$body_words = new Words();
// $authors = new Words();

$all_headlines = array();

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
	$article->subtitle = $headline_words->getSentence(20,40);
	$all_headlines[] = $article->headline;
	$article->sentences = array();

	for ($j = 0; $j < 10; $j++)
		$article->sentences[] = $body_words->getSentence(10,30);
		
	$article->body = implode(" ", $article->sentences);

	$new_articles[] = $article;

}

$page_title_source = implode(" ", $all_headlines);
$page_title->parseString( $page_title_source );
$titlemash = $page_title->getSentence(10,20);

?>
<!DOCTYPE html>
<html>
<head></head>
<body>

<h1><?php echo $titlemash; ?></h1>

<?php foreach ($new_articles as $article) { ?>
<article>
	<header>
		<h1><?php echo $article->headline; ?></h1>
		<h2 class="subtitle"><?php echo $article->subtitle; ?></h2>		
	</header>
	<p><?php echo $article->body; ?></p>
</article>

<?php } ?>

</body>
</html>