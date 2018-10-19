<?php
  require __DIR__ . '/vendor/autoload.php';
  use Twilio\TwiML\MessagingResponse;

  header("content-type: text/xml");
  $message = strtolower($_REQUEST['Body']);

  $defaults = ["A wah ya ask me dey?","Eee?","Who you?","Chupz"];

    $movies = findShowtimes(date("N"));
    $reply = "Caribbean Cinemas a show:\n";
    foreach($movies as $movie => $times){
      if($times){
        $reply .= "$movie: ".implode(", ",$times)."\n";
      }
    }

  switch($message){
    case "what is showing?":
      $movies = findShowtimes(date("N"));
      $reply = "Caribbean Cinemas a show:\n";
      foreach($movies as $movie => $times){
        if($times){
          $reply .= "$movie: ".implode(", ",$times)."\n";
        }
      }
      break;
    default:
      $reply = $defaults[rand(0,3)];
  }

  $response = new MessagingResponse();
  $response->message(
    $reply
  );

  echo $response;

  function findShowtimes($day){
    $showTimes = array();

    $resource = curl_init("https://caribbeancinemas.com/location/antigua/");
    curl_setopt($resource, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($resource);

    $dom = new DOMDocument();
    $dom->loadHTML($response);
    $xpath = new DOMXPath($dom);

    $movies = $xpath->query('//div[contains(@class,"three-fourth")]/h5/b');
    $timeStrings = array();
    foreach($xpath->query('//div[@class="column three-fourth"]/div[position()=2]') as $time){
      $timeStrings[] = $time->nodeValue;
    }

    for($i=0; $i < sizeof($movies); $i++){
      $movieTimes = trim($timeStrings[$i]);

      if($day <= 5){
        preg_match("/^MON-FRI\s+(?:(?:[0-9]\:[0-9]{2})\s+PM(?:,\s+)?)+/",$movieTimes,$matches);
        preg_match_all("/[0-9]\:[0-9]{2}\s+PM/",$matches[0],$matches);
        $times = $matches[0];
      }else if($day == 6){
        preg_match("/^SATURDAY\s+(?:(?:[0-9]\:[0-9]{2})\s+PM(?:,\s+)?)+/",$movieTimes,$matches);
        preg_match_all("/[0-9]\:[0-9]{2}\s+PM/",$matches[0],$matches);
        $times = $matches[0];
      }else{
        preg_match("/^SUN&HOL\s+(?:(?:[0-9]\:[0-9]{2})\s+PM(?:,\s+)?)+/",$movieTimes,$matches);
        preg_match_all("/[0-9]\:[0-9]{2}\s+PM/",$matches[0],$matches);
        $times = $matches[0];
      }

      $showTimes[trim($movies[$i]->nodeValue)] = $times;
    }

    return $showTimes;
  }
?>
