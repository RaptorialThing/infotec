<?php

/* 
Код написан чтобы вакансии с hh быстро добавлялись к нам, чтобы уже добавленные к нам, при изменении их на hh у нас тоже изменялись.
Вопросы нагрузки на сервер (наш и hh ru) на данном этапе игнорируются.

Быстрый парсер забирает свежедобавленые записи */

/*
Долгий парсер обновляет свой  участок раз в n минут

Так как диапазон страниц на hh ru большой долгих парсеров несколько и каждый обновляет свой диапазон, который он может обновить в указаный промежуток.

Так как появляются новые страницы, есть менеджер новых парсеров, который добавляет новый долгий парсер когда есть неотслеживаемые вакансии, которые не видит быстрый и которые не добавлены ни в один долгий пасрер.
*/

/*Быстрый парсер получает диапазон адресов, в которых меньше 2000 записей и парсит их раз в 5 минту, успевая за это время спарсить все данные если их там менее 2000 за 5 минут. Если за 5 минут более 2000 записей накопилось, старые добавляются в хранилище и создается новый долгий парсер на эти данные. А быстрый парсер начинает игнорирует данный участок, снова ожидая переполнения 2000 записей.*/

// Функция генерирует get запрос к API чтобы получить адреса в указанном диапазоне

function getNewVacanciesPages($startDate='2019-08-05T11:05:00',$stopDate='2019-08-05T11:10:00') {

		$url = 'https://api.hh.ru/vacancies?date_from='.$startDate.'&date_to='.$stopDate;

		$ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, "User-Agent: BlackPearl/1.0");
        
        $response = curl_exec($ch);
        curl_close($ch);

		$response = json_decode($response,JSON_UNESCAPED_UNICODE);
        // $pos = strpos($response,"/\"/");
        // $response = substr_replace($response, "", $pos, 1);
        // $last = strlen($response);
        // // $response = substr_replace($response, "", $pos, $last);
        // $response=rtrim($response,"\"");        

        // $response = preg_replace("/\\\\/", "",$response);
        return $response['pages'];
}

function getFastVacanciesTime() {
	$i = 2;
}

function setFastVacanciesTime() {
	$i = 1;
}

// Прибавляет 5 минут

function increaseStopDate($startDate,$increaseMinutes=5) {
	$startDate = strtotime($startDate);
	$stopDate = strtotime('+'.$increaseMinutes.' minutes', $startDate);
	$stopDate = date("Y:m:d H:i:s",$stopDate);
	$stopDate = new DateTime($stopDate);
	$stopDate = $stopDate->format('c');

	$stopDate = preg_replace("/\+03:00/","",$stopDate);

	return $stopDate;
}

$startDate='2019-08-05T12:35:00'; // будет браться из базы
getFastVacanciesTime();

$stopDate=increaseStopDate($startDate);

$pages = getNewVacanciesPages($startDate,$stopDate);
print "</br>";
print($startDate);
print "</br>";
print_r($pages);

for ($i=0; $i<$pages;$i++) {
	print("</br>");
	print("https://api.hh.ru/vacancies?date_from=".$startDate."&date_to=".$stopDate."&page=".$i);
	// запускаем парсер на эти старницы, он должен обработать их менее чем за 5 минут. Если добавлено в базу pages * 20 записей то далее, иначе опять этот диапазон парсим.

}
$startDate = $stopDate; 
print "</br>";
print($startDate); // будет записываться в базу

if (1) { // если есть спарсенные данные
setFastVacanciesTime();
}


// Функция запускает быстрый парсер и отслеживает сколько страниц в диапазоне

// Функция вызываетс если число страниц в диапазоне больше 2000 и повляются неспарсенные. Она отодвигате быстрый парсер вперед и добавляет новый долгий парсер на этот промежуток адресов

// Функция вызывает долгие парсеры работающие одновременно в паралелльных процессах.


?>