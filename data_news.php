#!/usr/bin/php

//-------------------------------------------------------------------------------
//  Scrape News
//-------------------------------------------------------------------------------
//  Author      Matteo Tempestini
//  Date        22 04 2017
//  License     MIT
//-------------------------------------------------------------------------------
//  Scrape News da http://comunicati.comune.prato.it/generali/
//-------------------------------------------------------------------------------

<?php

//crea file CSV degli anni 2004- 2017
//fonte notizie http://comunicati.comune.prato.it/generali/
//commentare le notizie che non si vogliono estrarre

data_news_anno("2004");
data_news_anno("2005");
data_news_anno("2006");
data_news_anno("2007");
data_news_anno("2009");
data_news_anno("2010");
data_news_anno("2011");
data_news_anno("2012");
data_news_anno("2013");
data_news_anno("2014");
data_news_anno("2015");
data_news_anno("2016");
data_news_anno("2017");

//estrae le notizie e crea un file CSV contenente tutti i dati delle notizie estratte
//formato del file CSV: "id","link","data","ora","canale","titolo","occhiello","catenaccio","testo"
function data_news_anno($year) {

  // nome del file CSV
  $file_name =$year . "_news.csv";

  //trova il MAX numero di notizie presenti nell'anno
  $url_all= "http://comunicati.comune.prato.it/generali/?action=elenco&anno=". $year;
  $max_news=find_max($url_all);

  //prepara e scrive l'header
  $header=array("id","link","data","ora","canale","titolo","occhiello","catenaccio","testo");
  $handle = fopen($file_name, "a");
  fputcsv($handle, $header);

  //per tutte le notizie prelevo i dati che mi interessa memorizzare
  for ($i = 1; $i <= (int)$max_news; $i++) {
      $i_8= sprintf("%08d",$i);
      $url= "http://comunicati.comune.prato.it/generali/?action=dettaglio&comunicato=14". $year . $i_8;
 	    $html= file_get_contents($url);

      if ($html != "")
      {
	         $dom = new DOMDocument();
	         $dom->loadHTML($html);
	         $xpath = new DOMXPath($dom);

      //CANALE
	    $my_xpath_query = "//*[@id='c-canale']";
	    $canale = $xpath->query($my_xpath_query);

      //ORA DATA
      $my_xpath_query = "//*[@id='c-data']";
	    $ora_data = $xpath->query($my_xpath_query);
      $ora_data_s = explode(" ", $ora_data->item(0)->nodeValue);
      $ora=$ora_data_s[1];
      $data=$ora_data_s[0];

      //TITOLO
      $my_xpath_query = "//*[@id='salta']";
	    $titolo = $xpath->query($my_xpath_query);

      //OCCHIELLO
      $my_xpath_query = "//*[@id='c-occhiello']";
	    $occhiello = $xpath->query($my_xpath_query);

      //CATENACCIO
      $my_xpath_query = "//*[@id='c-catenaccio']";
      $catenaccio = $xpath->query($my_xpath_query);

      //TESTO
      $my_xpath_query = "//*[@class='c-testo']";
      $testo = $xpath->query($my_xpath_query);

      //creo un elemento da scrivere nel file con tutti i campi estratti
      $data=array($i,$url,$data,$ora,$canale->item(0)->nodeValue,$titolo->item(0)->nodeValue,$occhiello->item(0)->nodeValue,$catenaccio->item(0)->nodeValue,$testo->item(0)->nodeValue);
      }
      else {
        # notizia non presente. scrive una riga vuota
        $data=array($i,$url,"","","","","","","notizia non presente");
      }
      //scrive sul file
      fputcsv($handle, $data);
  }
  fclose($handle);
}

//trova il massimo numero delle notizie presenti per un anno
function find_max($url)
{
  $html= file_get_contents($url);
  $dom = new DOMDocument();
  $dom->loadHTML($html);
  $xpath1 = new DOMXPath($dom);
  //FIND MAX FROM ULR
  $my_xpath_query = "//*[@id='content']/li[1]/a/@href";
  $max = $xpath1->query($my_xpath_query);
  $link=$max->item(0)->nodeValue;
  $number_max=substr($link, -8);
  $number_max = ltrim($number_max, '0');
  //print_r($number_max);
  return $number_max;
}
