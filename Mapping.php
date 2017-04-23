<?php
  include("Mapping_Function.php");
 ?>
<html>
  <head>
      <link rel="stylesheet" href="dist/css/bootstrap-theme.min.css">
      <link rel="stylesheet" href="dist/css/bootstrap.min.css">
      <link rel="stylesheet" href="dist/css/Css.css">
      <script src="dist/js/jquery.min.js"></script>
      <script src="dist/js/bootstrap.min.js"></script>
      <title>計算機組織 - 映射程式練習</title>
  </head>
  <body>
      <h1>Mapping Program Exercise</h1>
      <h2>Student ID:1410332049 Name:Yan Pin</h2>
      <div class="function-body">
          <div class="Calculation-Results">
              <?php
                $Array_input = Array(
                  "Function"  => @$_GET['Function'],
                  "Memory"    => @$_GET['Memory'],
                  "Cache"     => @$_GET['Cache'],
                  "Area"      => @$_GET['Area'],
                  "Address"   => @$_GET['Address'],
                  "Oak"       => @$_GET['Oak'],
                  "Button"    => @$_GET['Button']
                );

                $Mapping = New Mapping;
                echo $Mapping -> Main($Array_input);
              ?>
          </div>
          <form class="form-horizontal" method='GET' role="form" action="Mapping.php">
              <label for="exampleInputFile"> 映射選擇 </label>
              <div class="radio" style="margin-bottom: 15px;">
                  <label>
                    <input type="radio" name="Function" value="Direct" <?php if(@$_GET['Function'] == 'Direct'){ echo "checked";} ?> >
                    直接映射
                  </label>
                  <label>
                    <input type="radio" name="Function" value="Complete" <?php if(@$_GET['Function'] == 'Complete'){ echo "checked";} ?> >
                    完全關聯映射
                  </label>
                  <label>
                    <input data-toggle="collapse" data-target="#demo" type="radio" name="Function" value="Set" data-toggle="collapse" data-target="#demo"
                    <?php if(@$_GET['Function'] == 'Set'){ echo "checked";} ?> >
                    集合關聯映射
                  </label>
              </div>
              <!--  -->
              <div class="form-group">
                  <label for="inputEmail3" class="col-sm-2 control-label">記憶體大小</label>
                  <div class="col-sm-10">
                      <input Name='Memory' type="text" value='<?=@$_GET[' Memory ']?>' class="form-control" placeholder="記憶體大小 ( KB MB GB ) ex:(16MB)">
                  </div>
              </div>
              <div class="form-group">
                  <label for="inputEmail3" class="col-sm-2 control-label">快取大小</label>
                  <div class="col-sm-10">
                      <input Name='Cache' type="text" value='<?=@$_GET[' Cache ']?>' class="form-control" placeholder="快取大小 ( KB MB GB ) ex:(64KB)">
                  </div>
              </div>
              <div class="form-group">
                  <label for="inputEmail3" class="col-sm-2 control-label">區域大小</label>
                  <div class="col-sm-10">
                      <input Name='Area' type="text" value='<?=@$_GET[' Area ']?>' class="form-control" placeholder="區域大小 ( Bytes ) ex:(4B)">
                  </div>
              </div>
              <div class="form-group">
                  <label for="inputEmail3" class="col-sm-2 control-label">記憶體位址</label>
                  <div class="col-sm-10">
                      <input Name='Address' type="text" value='<?=@$_GET[' Address ']?>' class="form-control" placeholder="記憶體位址 ( 十六進位 ) ex:(017FFC)">
                  </div>
              </div>
              <!-- 集合映射需輸入 -->
              <div id="demo" class="collapse <?php if($_GET['Oak']!=null){echo " in ";} ?>">
                  <div class="form-group">
                      <label for="inputEmail3" class="col-sm-2 control-label">向數</label>
                      <div class="col-sm-10">
                          <input Name='Oak' type="text" value='<?=@$_GET[' Oak ']?>' class="form-control" placeholder="向數">
                      </div>
                  </div>
              </div>
              <button type="submit" Name='Button' value='Button' class="btn btn-default">送出</bu
            </form>
      </div>
  </body>
</html>
