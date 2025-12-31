<?php 
// api.php
class YsmPayApi{
    
    // 发送POST请求
    public static function HttpPost($url, $data){
        $header = array(
            'Content-Type: application/json; charset=UTF-8',
            'Accept: application/json',
            'User-Agent: */*'
        );
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // 跳过证书检查
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0); 
        // 注意：如果你是在本地测试，User-Agent 可能会报错，这里加个默认值
 
        curl_setopt($curl, CURLOPT_USERAGENT, $userAgent);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_AUTOREFERER, 1);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        // 在 curl_setopt 部分添加：
        curl_setopt($curl, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_2);
        curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');
        $result = curl_exec($curl);
     
        if (curl_errno($curl)) {
            return json_encode(['code'=>500, 'msg'=>'CURL Error: '.curl_error($curl)]);
        }
        curl_close($curl);
        return $result;
    }
    
    // 生成签名
    public static function HashSign(array $data, $secret){
        ksort($data); // 排序
        reset($data);
        $str = '';
        foreach ($data as $key => $row){
            // 跳过 hash 字段和空值
            if($key == 'hash' || $key == 'sign' || is_null($row) || $row === ''){
                continue;
            }
            if($str){
                $str .= '&';
            }
            $str .= "$key=$row";
        }
        // 进行 SHA256 加密
        return hash('sha256', $str . $secret, false);
    }
}
?>