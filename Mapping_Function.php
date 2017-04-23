<?php
 /**
  * Conversion 類別
  *   儲存單位轉換用的函數
  */
  class Conversion
  {
    /**
      * Zero_Fill 函數
      *   當取出 標籤 線路 字組 集合 後將數值補成完整十六進制
      */
    function Zero_Fill($Address){
      $Strlen = Strlen($Address);
      if($Strlen % 4 != 0){
        switch ($Strlen % 4){
          case 1:
            $Address = substr_replace($Address,'000', 0, 0);
            break;
          case 2:
            $Address = substr_replace($Address,'00', 0, 0);
            break;
          case 3:
            $Address = substr_replace($Address,'0', 0, 0);
            break;
        }
      }
      return $Address;
    }

    /**
      * Direct_Bit_Reorganization 函數
      * 進行單位轉換，十六進制轉二進制
      * input  : FF7FFC
      * Output : ArrAy(
      * [0] => 1111 [1] => 1111 [2] => 0111 [3] => 1111
      * [4] => 1111 [5] => 1100
      * [complete] => 111111110111111111111100
      */
    function Bit_Reorganization($Address){
      $Array_Address=str_split($Address);
      $complete = "";

      for($i=0;$i<=count($Array_Address)-1;$i++){
        $Array_base_convert[$i] = base_convert($Array_Address[$i], 16, 2);
        if( strlen($Array_base_convert[$i]) < 4 ){
          switch (strlen($Array_base_convert[$i])) {
            case 1:
              $Array_base_convert[$i] = substr_replace($Array_base_convert[$i],'000', 0, 0);
              break;
            case 2:
              $Array_base_convert[$i] = substr_replace($Array_base_convert[$i],'00', 0, 0);
              break;
            case 3:
              $Array_base_convert[$i] = substr_replace($Array_base_convert[$i],'0', 0, 0);
              break;
          }
        }
      }
      // 將切開的 四位元集組合在一起
      for($i=0;$i<=count($Array_base_convert)-1;$i++){
        $complete = $complete.$Array_base_convert[$i];
      }
      $Array_base_convert['complete'] = $complete;
      return $Array_base_convert;
    }
    /**
      * ConvertToBytes 函數
      *   進行單位轉換，將輸入的資料轉換成 Bytes
      *   可轉換單位 KB MB GB TB PB
      */
    function ConvertToBytes($from){
      $number=substr($from,0,-2);
      switch(strtoupper(substr($from,-2))){
          case "KB":
              return $number*1024;
          case "MB":
              return $number*pow(1024,2);
          case "GB":
              return $number*pow(1024,3);
          case "TB":
              return $number*pow(1024,4);
          case "PB":
              return $number*pow(1024,5);
          default:
              return substr($from,0,-1);;
      }
    }
    /**
      * Power 函數
      *   進行單位轉換，將 Bytes 傳換成二次方
      *
      */
    function ConvertToPower($Bytes){
      $Power = 0;
      while(pow(2,$Power)!=$Bytes) {
        $Power++;
      }
      return $Power;
    }
  }

  class Mapping extends Conversion{
    /**
      * Input_Protection 函數
      *   輸入資料判斷，只有判斷空值沒正規化
      */
    function Input_Protection($Array_Get_Data)
    {
      if($Array_Get_Data['Button'] != NULL){
        switch ($Array_Get_Data) {
          case $Array_Get_Data['Function'] == NULL:
            echo "<script>alert('映射選擇未選擇');</script>";
            break;

          case $Array_Get_Data['Memory'] == NULL:
            echo "<script>alert('記憶體大小空值');</script>";
            break;

          case $Array_Get_Data['Cache'] == NULL:
            echo "<script>alert('快取大小空值');</script>";
            break;

          case $Array_Get_Data['Area'] == NULL:
            echo "<script>alert('區域大小空值');</script>";
            break;

          case $Array_Get_Data['Address'] == NULL:
            echo "<script>alert('記憶體位址空值');</script>";
            break;

          default:
            if($Array_Get_Data['Function'] == 'Set'){
              if($Array_Get_Data['Oak'] != NULL){
                return(true);
              }else{
                echo "<script>alert('向數空值');</script>";
              }
            }else{
              // 不是Set 並通過檢查
              return(true);
            }
            break;
        }
      }
    }
    /**
      * Main 函數
      *   主要函數，處理三種 直接映射 完全關聯映射 集合關聯映射
      */
    function Main(Array $Array_Get_Data){
      $Input_Protection = $this->Input_Protection($Array_Get_Data);
      if($Input_Protection){
        //第一步驟 轉換單位(Bytes)
        $Array_Bytes_Data = array(
          'Memory'  => $this->ConvertToBytes($Array_Get_Data['Memory']),
          'Cache'   => $this->ConvertToBytes($Array_Get_Data['Cache']),
          'Area'   => $this->ConvertToBytes($Array_Get_Data['Area'])
        );
        //第二步驟 傳換成次方
        $Array_Power_Data= array(
          'Memory'  => $this->ConvertToPower($Array_Bytes_Data['Memory']),
          'Cache'   => $this->ConvertToPower($Array_Bytes_Data['Cache']),
          'Area'    => $this->ConvertToPower($Array_Bytes_Data['Area'])
        );

        $Array_Basis_Data = array(
          "Main_Memory_Blocks"  =>  $this->
            ConvertToPower($Array_Bytes_Data['Memory'] / $Array_Bytes_Data['Area']),
          "Cache_Lines"         =>  $this->
            ConvertToPower($Array_Bytes_Data['Cache'] / $Array_Bytes_Data['Area'])
        );
        $Binary_Address = $this-> Bit_Reorganization($Array_Get_Data['Address']);

        switch ($Array_Get_Data['Function']) {
          case 'Direct':
            $this->Direct($Array_Power_Data, $Array_Basis_Data, $Binary_Address);
            break;
          case 'Complete':
            $this->Complete($Array_Power_Data, $Array_Basis_Data, $Binary_Address);
            break;
          case 'Set':
            $this->Set($Array_Power_Data, $Array_Basis_Data, $Binary_Address, $Array_Bytes_Data, $Array_Get_Data);
              break;
          default:
            break;
        }
      }else{
        echo 'false';
      }
    }
    /**
      * Direct 函數
      *   直接映射 數值計算函數
      */
    function Direct(array $Array_Power_Data, array $Array_Basis_Data, Array $Array_Binary_Address)
    {

      $Bit_Address =
        ( $Array_Basis_Data['Main_Memory_Blocks'] - $Array_Basis_Data['Cache_Lines'] ) +
        $Array_Basis_Data['Cache_Lines'] + $Array_Power_Data['Area'];

        //取出 標籤所需要位元組
        $Legal =       substr(
          $Array_Binary_Address['complete'],
          0,
          ($Array_Basis_Data['Main_Memory_Blocks'] - $Array_Basis_Data['Cache_Lines'])
        );

        //取出 線路所需要位元組
        $Line =        substr(
          $Array_Binary_Address['complete'],
          ($Array_Basis_Data['Main_Memory_Blocks'] - $Array_Basis_Data['Cache_Lines']),
          $Array_Basis_Data['Cache_Lines']
        );

        //取出 字組所需要位元組
        $Word =  substr(
          $Array_Binary_Address['complete'],
          ($Array_Basis_Data['Main_Memory_Blocks'] - $Array_Basis_Data['Cache_Lines']) + $Array_Basis_Data['Cache_Lines']
        );

      $Array_View_Data = Array(
        'Main_Memory_Blocks'  => $Array_Basis_Data['Main_Memory_Blocks'],
        'Cache_Lines'         => $Array_Basis_Data['Cache_Lines'],

        's' => $Array_Basis_Data['Main_Memory_Blocks'],
        'r' => $Array_Basis_Data['Cache_Lines'],
        'w' => $Array_Power_Data['Area'],

        'Tag'         => $Array_Basis_Data['Main_Memory_Blocks'] - $Array_Basis_Data['Cache_Lines'],
        'Line'        => $Array_Basis_Data['Cache_Lines'],
        'Word'        => $Array_Power_Data['Area'],
        'Bit_Address' => $Bit_Address,

        'Address_Length'          => $Array_Basis_Data['Main_Memory_Blocks'] + $Array_Power_Data['Area'],
        'Addressable_Unit_Length' => $Array_Basis_Data['Main_Memory_Blocks'] + $Array_Power_Data['Area'],
        'Memory_Locality'         => $Array_Power_Data['Area'],

        'Memory_Locality_Length'  => $Array_Basis_Data['Main_Memory_Blocks'],
        'Cache_Line_Length'       => $Array_Basis_Data['Cache_Lines'],
        'Tag_Sum'                 => $Array_Basis_Data['Main_Memory_Blocks'] - $Array_Basis_Data['Cache_Lines'],

        'Answer_tag'              => base_convert($this->Zero_Fill($Legal), 2, 16),
        'Answer_Cache_line_number'=> base_convert($this->Zero_Fill($Line), 2, 16),
        'Answer_Word'             => base_convert($this->Zero_Fill($Word), 2, 16)
      );

      $this->View_Direct($Array_View_Data);
    }

    function View_Direct($Array_View_Data){
    print <<<EOT
          Main Memory Blocks : 2<sup> {$Array_View_Data['Main_Memory_Blocks']} </sup><br/>
          Cache Lines: 2<sup> {$Array_View_Data['Cache_Lines']} </sup><br/>
        <hr/>
          s: {$Array_View_Data['s']}
          r: {$Array_View_Data['r']}
          w: {$Array_View_Data['w']} <Br/>

          標籤(  {$Array_View_Data['Tag']}  )
          線路(  {$Array_View_Data['Line']} )
          字組(  {$Array_View_Data['Word']} )<br/>
          位元位址 = {$Array_View_Data['Bit_Address']}
        <hr/>
          位址長度 = {$Array_View_Data['Address_Length']} Bits <br/>
          可定址單位總數 = 2<sup>{$Array_View_Data['Addressable_Unit_Length']}</sup> Byte <br/>
          記憶區塊 = 2<sup>{$Array_View_Data['Memory_Locality']}</sup> Byte <br/>

          記憶區塊總數 = 2<sup>{$Array_View_Data['Memory_Locality_Length']}</sup> Byte <br/>
          快取線路總數 = 2<sup>{$Array_View_Data['Cache_Line_Length']}</sup> Byte <br/>
          標籤大小 = 2<sup>{$Array_View_Data['Tag_Sum']}</sup> Byte <br/>

        <hr/>
          <h1>
            標籤 = {$Array_View_Data['Answer_tag']}
            快取線路編號 = {$Array_View_Data['Answer_Cache_line_number']}
            字組 = {$Array_View_Data['Answer_Word']}
          </h1>
EOT;
    }

    function Complete(array $Array_Power_Data, array $Array_Basis_Data, Array $Array_Binary_Address)
    {
      //取出 標籤所需要位元組
      $Legal = substr(
        $Array_Binary_Address['complete'],
        0,
        $Array_Basis_Data['Main_Memory_Blocks']
      );

      //取出 字組所需要位元組
      $Word = substr(
        $Array_Binary_Address['complete'],
        $Array_Basis_Data['Main_Memory_Blocks']
      );

      $Array_View_Data = Array(
        'Main_Memory_Blocks'  => $Array_Basis_Data['Main_Memory_Blocks'],
        'Cache_Lines'         => $Array_Basis_Data['Cache_Lines'],

        's' => $Array_Basis_Data['Main_Memory_Blocks'],
        'r' => $Array_Basis_Data['Cache_Lines'],
        'w' => $Array_Power_Data['Area'],

        'Tag'         => $Array_Basis_Data['Main_Memory_Blocks'],
        'Word'        => $Array_Power_Data['Area'],
        'Bit_Address' => $Array_Basis_Data['Main_Memory_Blocks'] + $Array_Power_Data['Area'],

        'Address_Length'          => $Array_Basis_Data['Main_Memory_Blocks'] + $Array_Power_Data['Area'],
        // 位址長度 = (s + w) 位元
        'Addressable_Unit_Length' => $Array_Basis_Data['Main_Memory_Blocks'] + $Array_Power_Data['Area'],
        // 可定址單元數目 = 2 ^ ( s + w ) 位元組
        'Memory_Locality'         => $Array_Power_Data['Area'],
        // 記憶區塊 = 快取線路 = 2 ^ w 位元組
        'Memory_Locality_Length'  => $Array_Basis_Data['Main_Memory_Blocks'],
        // 主記憶體區塊數目 = 2 ^ ( s + w ) / ( 2 ^ w ) = 2 ^ s
        'Cache_Line_Length'       => "",
        // 快取線路數目 = 未定
        'Tag_Sum'                 => $Array_Basis_Data['Cache_Lines'],
        // 標籤大小 = s 位元

        'Answer_tag'              => base_convert($this->Zero_Fill($Legal), 2, 16),
        'Answer_Word'             => base_convert($this->Zero_Fill($Word), 2, 16)
      );
      $this->View_Complete($Array_View_Data);
    }
    function View_Complete($Array_View_Data){
    print <<<EOT
          Main Memory Blocks : 2<sup> {$Array_View_Data['Main_Memory_Blocks']} </sup><br/>
          Cache Lines: 2<sup> {$Array_View_Data['Cache_Lines']} </sup><br/>
        <hr/>
          s: {$Array_View_Data['s']}
          r: {$Array_View_Data['r']}
          w: {$Array_View_Data['w']} <Br/>

          標籤(  {$Array_View_Data['Tag']}  )
          字組(  {$Array_View_Data['Word']} )<br/>
          位元位址 = {$Array_View_Data['Bit_Address']}
        <hr/>
          位址長度 = {$Array_View_Data['Address_Length']} bit <br/>
          可定址單位總數 = 2<sup>{$Array_View_Data['Addressable_Unit_Length']}</sup> Byte <br/>
          記憶區塊 = 2<sup>{$Array_View_Data['Memory_Locality']}</sup> Byte <br/>

          記憶區塊總數 = 2<sup>{$Array_View_Data['Memory_Locality_Length']}</sup> Byte <br/>
          快取線路總數 = 未定 <br/>
          標籤大小 = 2<sup>{$Array_View_Data['Tag_Sum']}</sup> Bit <br/>

        <hr/>
          <h1>
            標籤: {$Array_View_Data['Answer_tag']}
            字組: {$Array_View_Data['Answer_Word']}
          </h1>
EOT;
    }

    function Set(array $Array_Power_Data, array $Array_Basis_Data, Array $Array_Binary_Address, Array $Array_Bytes_Data, Array $Array_Get_Data)
    {
      $Bit_Area = ( pow(2, $Array_Power_Data['Area']) * 8 ) / 2;
      $D = $Bit_Area / $Array_Get_Data['Oak'];
      $Set_Length = $this->ConvertToPower($D * 1024);
      //集合位元

      $Tag_Sum = $Array_Basis_Data['Main_Memory_Blocks'] - $Set_Length;
      // 標籤位元

      //取出 標籤所需要位元
      $Legal = substr(
        $Array_Binary_Address['complete'],
        0,
        $Tag_Sum
      );
      // 取出 集合所需要位元
      $Set = substr(
        $Array_Binary_Address['complete'],
        $Tag_Sum,
        $Set_Length
      );
      //取出 字組所需要位元
      $Word = substr(
        $Array_Binary_Address['complete'],
        $Tag_Sum + $Set_Length
      );

      $Array_View_Data = Array(
        'Main_Memory_Blocks'  => $Array_Basis_Data['Main_Memory_Blocks'],
        'Cache_Lines'         => $Array_Basis_Data['Cache_Lines'],

        's' => $Array_Basis_Data['Main_Memory_Blocks'],
        'r' => $Array_Basis_Data['Cache_Lines'],
        'w' => $Array_Power_Data['Area'],


        'Tag'         => $Array_Basis_Data['Main_Memory_Blocks'],
        'Word'        => $Array_Power_Data['Area'],
        'Bit_Address' => $Array_Basis_Data['Main_Memory_Blocks'] + $Array_Power_Data['Area'],

        'Address_Length'          => $Array_Basis_Data['Main_Memory_Blocks'] + $Array_Power_Data['Area'],
        // • 位址長度 = (s + w) 位元
        'Addressable_Unit_Length' => $Array_Basis_Data['Main_Memory_Blocks'] + $Array_Power_Data['Area'],
        // • 可定址單元數目 = 2 ^ ( s + w ) 位元組
        'Memory_Locality'         => $Array_Power_Data['Area'],
        // • 記憶區塊 = 快取線路 = 2 ^ w 位元組
        'Memory_Locality_Length'  => $Set_Length,
        // • 主記憶體區塊數目 = 記憶體大小 / k
        'Set_Cache_Line_Length'   => $Array_Get_Data['Oak'],
        // • 集合內快取線路數目 = k
        'Set_Length'              => $Set_Length,
        // • 集合數目 v = 2d
        'Cache_Line_Length'       => "",
        // • 快取線路總數 = kv = k * 2d
        'Tag_Sum'                 => $Tag_Sum,
        // • 標籤大小 = (s – d) 位元
        //這裡
        'Answer_Tag'              => base_convert($this->Zero_Fill($Legal), 2, 16),
        'Answer_Set'              => base_convert($this->Zero_Fill($Set), 2, 16),
        'Answer_Word'             => base_convert($this->Zero_Fill($Word), 2, 16)
      );
      $this->View_Set($Array_View_Data);
    }
    function View_Set($Array_View_Data){
      print <<<EOT
            Main Memory Blocks : 2<sup> {$Array_View_Data['Main_Memory_Blocks']} </sup><br/>
            Cache Lines: 2<sup> {$Array_View_Data['Cache_Lines']} </sup><br/>
          <hr/>
            s: {$Array_View_Data['s']}
            r: {$Array_View_Data['r']}
            w: {$Array_View_Data['w']} <Br/>

            標籤(  {$Array_View_Data['Tag']}  )
            字組(  {$Array_View_Data['Word']} )<br/>
            位元位址: {$Array_View_Data['Bit_Address']}
          <hr/>
            位址長度 = {$Array_View_Data['Address_Length']} bit <br/>
            可定址單位總數 = 2<sup>{$Array_View_Data['Addressable_Unit_Length']}</sup> Byte <br/>
            記憶區塊 = 快取線路 = 2<sup>{$Array_View_Data['Memory_Locality']}</sup> Byte <br/>

            主記憶區塊數目 = 2<sup>{$Array_View_Data['Memory_Locality_Length']}</sup>  <br/>
            集合內快取線路數目 = {$Array_View_Data['Set_Cache_Line_Length']} <br/>
            集合數目 = 2<sup>{$Array_View_Data['Set_Length']}</sup><br/>

            快取線路總數 = {$Array_View_Data['Set_Length']} * 2<sup>{$Array_View_Data['Memory_Locality_Length']}</sup><br/>
            標籤大小 =  {$Array_View_Data['Tag_Sum']} bit
          <hr/>
            <h1>
              標籤: {$Array_View_Data['Answer_Tag']}
              集合: {$Array_View_Data['Answer_Set']}
              字組: {$Array_View_Data['Answer_Word']}
            </h1>
EOT;
    }
  }
  ?>
