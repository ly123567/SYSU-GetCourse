<?php

/* 参数 */
define("_LYSESSIONID", ''); //Cookies
define("_USER", ''); //Cookies
define("_CLASSID", 'DCS111'); //课程编号
define("_DELAY_MIN_SECOND", 100); //单位为0.1s
define("_DELAY_MAX_SECOND", 300); //单位为0.1s

echo "Start Request.\n";
while(true) {
    //深夜减少请求数量
    if (date("H") < 8 && date("H") >= 0) sleep(600);

    //计算毫秒数
    list($msec, $sec) = explode(' ', microtime());
    $msectime = (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);

    $header = array(
        'Cookie: LYSESSIONID=' . _LYSESSIONID . '; user=' . _USER,
        'Origin: https://uems.sysu.edu.cn',
        'Accept-Encoding: gzip, deflate, br',
        'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8',
        'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/72.0.3626.119 Safari/537.36',
        'Content-Type: application/json;charset=UTF-8',
        'Accept: application/json, text/plain, */*',
        'Referer: https://uems.sysu.edu.cn/jwxt/mk/courseSelection/',
        'lastAccessTime: ' . $msectime, 
        'X-Requested-With: XMLHttpRequest',
        'moduleId: null',
        'menuId: null'
    );

    /* List */
    //TO-DO: $content部分参数可能有变动
    $content = '{"pageNo":1,"pageSize":10,"param":{"semesterYear":"2018-2","selectedType":"1","selectedCate":"21","hiddenConflictStatus":"0","hiddenSelectedStatus":"0","collectionStatus":"0"}}';
    $response = httpPost('https://uems.sysu.edu.cn/jwxt/choose-course-front-server/classCourseInfo/course/list?_t=' . time(), $header, $content);
    $json = json_decode($response, true);
    $classid = "";
    for ($i = 0; $i < $json['data']['total']; ++$i) {
        if ($json['data']['rows'][$i]['courseNum'] == _CLASSID) {
            $selectedNum = intval($json['data']['rows'][$i]['courseSelectedNum']);
            $basedNum = $json['data']['rows'][$i]['baseReceiveNum'];
            if ($selectedNum < $basedNum) {
                $classid = $json['data']['rows'][$i]['teachingClassId'];
            } else {
                echo date("[Y/m/d-H:i:s] ") . "CourseSelectedNum Full(" . $selectedNum . ").\n";
            }
            break;
        }
    }

    if ($classid !== "") {
        /* Select */
        //TO-DO: $content部分参数可能有变动
        $content = '{"clazzId":"' . $classid . '","selectedType":"1","selectedCate":"21","check":true}';
        $response = httpPost('https://uems.sysu.edu.cn/jwxt/choose-course-front-server/classCourseInfo/course/choose?_t=' . time(), $header, $content);
        $json = json_decode($response, true);
        $response = iconv('UTF-8', 'GBK//IGNORE', $response);
        echo date("[Y/m/d-H:i:s] ") . $response . "\n";
        if ($json['code'] == 200 || $json['code'] == 53000000) {
            echo "Success!\n";
            break;
        }
    }

    sleep(mt_rand(_DELAY_MIN_SECOND, _DELAY_MAX_SECOND) * 0.1);
}

function httpPost($url, $header, $content){
    $ch = curl_init();
    if (substr($url, 0,5) == 'https') {
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); //跳过证书检查
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);  //从证书中检查SSL加密算法是否存在
    }
    //curl_setopt($ch, CURLOPT_PROXY, "127.0.0.1");
    curl_setopt($ch, CURLOPT_ENCODING , "");
	//curl_setopt($ch, CURLOPT_PROXYPORT, 8888);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
    $response = curl_exec($ch);
    if ($error = curl_error($ch)) {
        die($error);
    }
    curl_close($ch);
    return $response;
}