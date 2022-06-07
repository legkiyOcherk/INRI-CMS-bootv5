<?
/**
 * @author Mayer Roman (majer.rv@gmail.com)
 */

class Image {

	/**
	 * @return Image
	 */
	static function Factory() {
		return new self();
	}


	public $sizes = array(120, 200, 800);


	private $image, $size, $width, $height;


	function getImageFromFile($file) {
		if (!file_exists($file)) return false;
		$size = getimagesize($file);
		if ($size === false) return false;
		$this->image = imagecreatefromjpeg($file);
	}


	/**
	 * @param int $src исходной файл
	 * @param int $dest генерируемый файл
	 * @param int $width ширина генерируемого изображения
	 * @param int $height высота генерируемого изображения (null = пропорционально)
	 * @param hex $rgb цвет фона, по умолчанию - белый
	 * @param int $quality качество генерируемого JPEG, по умолчанию - максимальное 
	 * @return 
	 */
	function resize($src, $dest, $width, $height = null, $rgb = 0xFFFFFF, $quality = 100) {
		if (!file_exists($src)) return false;

		$size = getimagesize($src);

		if ($size === false) return false;

		// Определяем исходный формат по MIME-информации, предоставленной
		// функцией getimagesize, и выбираем соответствующую формату
		// imagecreatefrom-функцию.
		$format = strtolower(substr($size['mime'], strpos($size['mime'], '/')+1));
		$icfunc = "imagecreatefrom" . $format;
		if (!function_exists($icfunc)) return false;
		
		$this->type = $format;
		$last_symbols = substr($dest, -5);
		if (strpos($last_symbols, $format) === false) {
      //В том случае если jpg чтобы не переименовывалось в jpeg
      $arr = explode(".", $dest);
      $perenennaya = end($arr);
      if(strtolower($perenennaya) != 'jpg'){
        $dest = "$dest.$format";
      }
      
      /*      $arr = explode(".", $dest);
      $perenennaya = end($arr);
      if(strtolower($perenennaya) != 'jpg'){
        $dest = "$dest.$format";
      }*/
		}

		$x_ratio = $width / $size[0];
		if (is_null($height)) {
			if ($size[0] < $size[1]) {
				$height = $width;
				$k = $size[1] / $height;
				$width = (int) $size[0] / $k;
			} else {
				$k = $size[0] / $width;
				$height = (int) $size[1] / $k;
			}
		}
		if (($width > $size[0]) and ($height > $size[1])) {
//			$_SESSION['msg'] = $src." &rarr; ".$dest;
			copy($src, $dest);
			return;
		}
		$y_ratio		=	$height / $size[1];
		$ratio			=	min($x_ratio, $y_ratio);
		$use_x_ratio	=	($x_ratio == $ratio);
		
		$new_width		=	$use_x_ratio  ? $width  : floor($size[0] * $ratio);
		$new_height		=	!$use_x_ratio ? $height : floor($size[1] * $ratio);
		$new_left		=	$use_x_ratio  ? 0 : floor(($width - $new_width) / 2);
		$new_top		=	!$use_x_ratio ? 0 : floor(($height - $new_height) / 2);

		$isrc = $icfunc($src);
		$idest = imagecreatetruecolor($width, $height);

		imagefill($idest, 0, 0, $rgb);
		$this->image = imagecopyresampled($idest, $isrc, $new_left, $new_top, 0, 0, $new_width, $new_height, $size[0], $size[1]);

		imagejpeg($idest, $dest, $quality);
		imagedestroy($isrc);
		imagedestroy($idest);
    
    return $dest;
	}



	function waterMark($str, $color = 'fff') {
		$im = $this->image;
		$angle = 90;
		$x = imagesx($im) - 10;
		$y = imagesy($im) - 10;
		$color = imagecolorallocate($im, 255, 255, 255);
		$font = $_SERVER["DOCUMENT_ROOT"].'i/arial.ttf';

		imagefttext($im, 10, $angle, $x, $y, $color, $font, $str);
		$this->image = $im;
		
	}


	function show() {
		header("Content-Type: image/jpeg");
		imagejpeg($this->image);
	}


	function save($fname, $quality = 100) {
		imagejpeg($this->image, $dest, $quality);
	}
  
  static function corpImg($debug = 0){
    $output = '';
    $output .= '
      <!-- Cropper -->  
      <style>
      @media (min-width: 1200px) {
        #img_modal_dialog.modal-lg{
          width: 80%;
        }
      }
      .cropper .docs-buttons>.btn, 
      .cropper .docs-buttons>.btn-group, 
      .cropper .docs-buttons>.form-control {
        margin-right: 5px;
        margin-bottom: 10px;
      }
      .cropper .docs-data>.input-group, 
      .cropper .docs-toggles>.btn, 
      .cropper .docs-toggles>.btn-group, 
      .cropper .docs-toggles>.dropdown, .cropper .img-preview {
        margin-bottom: 10px;
      }
      </style>
      <link href="'.IA_URL.'admin_style/vendor/cropper/dist/cropper.min.css" rel="stylesheet">
      <script src="'.IA_URL.'admin_style/vendor/cropper/dist/cropper.min.js"></script>';
    if($debug){
      $output .= '
      <script src="'.IA_URL.'admin_style/vendor/cropper/custom/corpper.custom.js?'.rand(0, 100).'"></script>';
    }else{
      $output .= '
      <script src="'.IA_URL.'admin_style/vendor/cropper/custom/corpper.custom.js"></script>';
    }
    
    if($debug){
      $output .= self::getCorpImgFormDebug();
    }else{
      $output .= self::getCorpImgForm();
    }
    


    return $output;
  }
  
  static function getCorpImgCenterBtn(){
    $output = '';
    $output .= '
                  <div class = "row">
                    <div class="col-12">
                    
                      <div class="docs-toggles">
                        <h4 class = "">Кадрирование</h4>
                        <div class="btn-group btn-group-justified" data-toggle="buttons">
                          
                          <label class="btn btn-primary active">
                            <input type="radio" class="sr-only" id="aspectRatio0" name="aspectRatio" value="1.7777777777777777">
                            <span class="docs-tooltip" data-toggle="tooltip" title="Кадр: 16 / 9">
                              16:9
                            </span>
                          </label>
                          <label class="btn btn-primary">
                            <input type="radio" class="sr-only" id="aspectRatio1" name="aspectRatio" value="1.3333333333333333">
                            <span class="docs-tooltip" data-toggle="tooltip" title="Кадр: 4 / 3">
                              4:3
                            </span>
                          </label>
                          <label class="btn btn-primary">
                            <input type="radio" class="sr-only" id="aspectRatio2" name="aspectRatio" value="1">
                            <span class="docs-tooltip" data-toggle="tooltip" title="aspectRatio: 1 / 1">
                              1:1
                            </span>
                          </label>
                          <label class="btn btn-primary">
                            <input type="radio" class="sr-only" id="aspectRatio3" name="aspectRatio" value="0.6666666666666666">
                            <span class="docs-tooltip" data-toggle="tooltip" title="Кадр: 2 / 3">
                              2:3
                            </span>
                          </label>
                          <label class="btn btn-primary">
                            <input type="radio" class="sr-only" id="aspectRatio4" name="aspectRatio" value="NaN">
                            <span class="docs-tooltip" data-toggle="tooltip" title="Свободное">
                              Свободное
                            </span>
                          </label>
                        </div>
                        
                        <h4 class = "">Режим кадрирования</h4>
                        <div class="btn-group btn-group-justified" data-toggle="buttons">
                          <label class="btn btn-primary active">
                            <input type="radio" class="sr-only" id="viewMode0" name="viewMode" value="0" checked>
                            <span class="docs-tooltip" data-toggle="tooltip" title="Режим 1">
                              Режим 1
                            </span>
                          </label>
                          <label class="btn btn-primary">
                            <input type="radio" class="sr-only" id="viewMode1" name="viewMode" value="1">
                            <span class="docs-tooltip" data-toggle="tooltip" title="Режим 2">
                              Режим 2
                            </span>
                          </label>
                          <label class="btn btn-primary">
                            <input type="radio" class="sr-only" id="viewMode2" name="viewMode" value="2">
                            <span class="docs-tooltip" data-toggle="tooltip" title="Режим 3">
                              Режим 3
                            </span>
                          </label>
                          <label class="btn btn-primary">
                            <input type="radio" class="sr-only" id="viewMode3" name="viewMode" value="3">
                            <span class="docs-tooltip" data-toggle="tooltip" title="Режим 4">
                              Режим 4
                            </span>
                          </label>
                        </div>
                      </div><!-- /.docs-buttons -->
                    </div>
   
                  </div>
    ';
    
    return $output;
  }
  
  static function getCorpImgRightBtn(){
    $output = '';
    
    $output .= '
                    <h4 class = "">Инструменты</h4>
                    <div class="docs-buttons">
                      <div class="btn-group">
                        <button type="button" class="btn btn-primary" data-method="setDragMode" data-option="move" title="Перемещение">
                          <span class="docs-tooltip" data-toggle="tooltip" title="Перемещение">
                            <span class="fa fa-arrows"></span>
                          </span>
                        </button>
                        <button type="button" class="btn btn-primary" data-method="setDragMode" data-option="crop" title="Рамка">
                          <span class="docs-tooltip" data-toggle="tooltip" title="Рамка">
                            <span class="fa fa-crop"></span>
                          </span>
                        </button>
                      </div>

                      <div class="btn-group">
                        <button type="button" class="btn btn-primary" data-method="zoom" data-option="0.1" title="Увеличить">
                          <span class="docs-tooltip" data-toggle="tooltip" title="Увеличить">
                            <span class="fa fa-search-plus"></span>
                          </span>
                        </button>
                        <button type="button" class="btn btn-primary" data-method="zoom" data-option="-0.1" title="Уменьшить">
                          <span class="docs-tooltip" data-toggle="tooltip" title="Уменьшить">
                            <span class="fa fa-search-minus"></span>
                          </span>
                        </button>
                      </div>

                      <div class="btn-group">
                        <button type="button" class="btn btn-primary" data-method="move" data-option="-10" data-second-option="0" title="Влево">
                          <span class="docs-tooltip" data-toggle="tooltip" title="Влево">
                            <span class="fa fa-arrow-left"></span>
                          </span>
                        </button>
                        <button type="button" class="btn btn-primary" data-method="move" data-option="10" data-second-option="0" title="Вправо">
                          <span class="docs-tooltip" data-toggle="tooltip" title="Вправо">
                            <span class="fa fa-arrow-right"></span>
                          </span>
                        </button>
                        <button type="button" class="btn btn-primary" data-method="move" data-option="0" data-second-option="-10" title="Вверх">
                          <span class="docs-tooltip" data-toggle="tooltip" title="Вверх">
                            <span class="fa fa-arrow-up"></span>
                          </span>
                        </button>
                        <button type="button" class="btn btn-primary" data-method="move" data-option="0" data-second-option="10" title="Вниз">
                          <span class="docs-tooltip" data-toggle="tooltip" title="Вниз">
                            <span class="fa fa-arrow-down"></span>
                          </span>
                        </button>
                      </div>

                      <div class="btn-group">
                        <button type="button" class="btn btn-primary" data-method="rotate" data-option="-45" title="Поворот влево">
                          <span class="docs-tooltip" data-toggle="tooltip" title="Поворот влево 45">
                            <span class="fa fa-rotate-left"></span>
                          </span>
                        </button>
                        <button type="button" class="btn btn-primary" data-method="rotate" data-option="45" title="Поворот вправо">
                          <span class="docs-tooltip" data-toggle="tooltip" title="Поворот вправо 45">
                            <span class="fa fa-rotate-right"></span>
                          </span>
                        </button>
                      </div>

                      <div class="btn-group">
                        <button type="button" class="btn btn-primary" data-method="scaleX" data-option="-1" title="Отразить по горизонтали">
                          <span class="docs-tooltip" data-toggle="tooltip" title="Отразить по горизонтали">
                            <span class="fa fa-arrows-h"></span>
                          </span>
                        </button>
                        <button type="button" class="btn btn-primary" data-method="scaleY" data-option="-1" title="Отразить по вертикали">
                          <span class="docs-tooltip" data-toggle="tooltip" title="Отразить по вертикали">
                            <span class="fa fa-arrows-v"></span>
                          </span>
                        </button>
                      </div>

                      <div class="btn-group">
                        <label class="btn btn-primary btn-upload" for="inputImage" title="Загрузить файл изображения">
                          <input type="file" class="sr-only" id="inputImage" name="file" accept="image/*">
                          <span class="docs-tooltip" data-toggle="tooltip" title="Импортировать изображение">
                            <span class="fa fa-upload"></span>
                          </span>
                        </label>
                      </div>
                    </div>';
                      
    $output .= '
                      <h4 class = "">Свойства изображения</h4>
                      <div class="docs-data">
                        <div class="input-group input-group-sm">
                          <label class="input-group-addon" for="dataX">X</label>
                          <input type="text" class="form-control" id="dataX" placeholder="x">
                          <span class="input-group-addon">px</span>
                        </div>
                        <div class="input-group input-group-sm">
                          <label class="input-group-addon" for="dataY">Y</label>
                          <input type="text" class="form-control" id="dataY" placeholder="y">
                          <span class="input-group-addon">px</span>
                        </div>
                        <div class="input-group input-group-sm">
                          <label class="input-group-addon" for="dataWidth">Ширина</label>
                          <input type="text" class="form-control" id="dataWidth" placeholder="Ширина">
                          <span class="input-group-addon">px</span>
                        </div>
                        <div class="input-group input-group-sm">
                          <label class="input-group-addon" for="dataHeight">Высота</label>
                          <input type="text" class="form-control" id="dataHeight" placeholder="Высота">
                          <span class="input-group-addon">px</span>
                        </div>
                        <div class="input-group input-group-sm">
                          <label class="input-group-addon" for="dataRotate">Поворот</label>
                          <input type="text" class="form-control" id="dataRotate" placeholder="Поворот">
                          <span class="input-group-addon">deg</span>
                        </div>
                        <div class="input-group input-group-sm">
                          <label class="input-group-addon" for="dataScaleX">Маштаб X</label>
                          <input type="text" class="form-control" id="dataScaleX" placeholder="Маштаб X">
                        </div>
                        <div class="input-group input-group-sm">
                          <label class="input-group-addon" for="dataScaleY">Маштаб Y</label>
                          <input type="text" class="form-control" id="dataScaleY" placeholder="Маштаб Y">
                        </div>
                      </div>';
    $output .= '
                        <button type="button" class="btn btn-primary" id = "setCroppData">
                          <span class="docs-tooltip" data-toggle="tooltip" title="" data-original-title="Применить свойства">
                            Применить свойства
                          </span>
                        </button>
                      ';
                      /*<div class="docs-buttons">
                        <button type="button" class="btn btn-primary" data-method="getData" data-option="" data-target="#putData">
                          <span class="docs-tooltip" data-toggle="tooltip" title="" data-original-title="$().cropper(&quot;getData&quot;)" aria-describedby="tooltip622196">
                            Get Data
                          </span><div class="tooltip fade top in" role="tooltip" id="tooltip622196" style="top: 64px; left: -11px; display: block;"><div class="tooltip-arrow" style="left: 50%;"></div><div class="tooltip-inner">$().cropper("getData")</div></div>
                        </button>
                        <button type="button" class="btn btn-primary" data-method="setData" data-target="#putCroppData">
                          <span class="docs-tooltip" data-toggle="tooltip" title="" data-original-title="$().cropper(&quot;setData&quot;, data)">
                            Set Data
                          </span>
                        </button>
                        

                        <textarea id="putData" class="form-control" style = "min-height: 100px;">
                        </textarea>
                        <textarea id = "putCroppData" class="form-control" style = "min-height: 100px;">
                        </textarea>
                      </div>*/
#<input type="text" class="form-control" id="putData" placeholder="Get data to here or set data with this value">
                    
    return $output;
  }
  
  static function getCorpImgForm(){
    $output = '';
    
    $output .= '
      <!-- Modal -->
      <div class="modal fade" id="img_modal" role="dialog" aria-labelledby="modalLabel" tabindex="-1">
        <div id = "img_modal_dialog" class="modal-dialog modal-lg" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <div class = "row">
                <div class = "col-xs-10"><h4 class="modal-title" id="modalLabel">Редактор изображения</h4></div>
                <div class = "col-xs-2">
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
              </div>
            </div>
            
            <div class="modal-body">
              <!-- image cropping -->
              <div class="cropper">
                <div class="row">
                  <div class="col-md-9">
                    <div class="img-container">
                      <img id="image" src="" alt="Picture" width = "100%">
                    </div>
                    '.self::getCorpImgCenterBtn().'
                  </div>
                  <div class="col-md-3">
                    '.self::getCorpImgRightBtn().'
                  </div>
                </div>

              </div>
              <!-- /image cropping -->
            </div>
            
            <div class="modal-footer">
            
              <!-- <button type="button" class="btn btn-sm btn-default" data-dismiss="modal">Закрыть</button>-->
              <div class="docs-toggles">
                <div class="docs-buttons">
                  <button type="button" class="btn btn-success" data-method="getCroppedCanvas">
                    <span class="docs-tooltip" data-toggle="tooltip" title="Сохранить">
                      Сохранить
                    </span>
                  </button>
                </div>
              </div>
              
            </div>
            
          </div>
        </div>
      </div>
    
    <!-- Show the cropped image in modal -->
    <div class="modal fade docs-cropped" id="getCroppedCanvasModal" aria-hidden="true" aria-labelledby="getCroppedCanvasTitle" role="dialog" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title" id="getCroppedCanvasTitle">Cropped</h4>
          </div>
          <div class="modal-body"></div>
          <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            <a class="btn btn-primary" id="download" href="javascript:void(0);" download="cropped.png">Download</a>
          </div>
        </div>
      </div>
    </div><!-- /.modal -->
    ';
    
    return $output;
  }
  
  function getCorpImgFormDebug(){ 
    $output = '';
    
    /*
    <!-- Button trigger modal -->
      <button type="button" class="btn btn-primary" data-target="#img_modal" data-toggle="modal">
        Launch the demo
      </button>
    */
 
    $output .= '
      <!-- Modal -->
      <div class="modal fade" id="img_modal" role="dialog" aria-labelledby="modalLabel" tabindex="-1">
        <div class="modal-dialog modal-lg" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="modalLabel">Cropper</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <!-- image cropping -->
                <div class="cropper">
                  <div class="row">
                    <div class="col-md-9">
                      <div class="img-container">
                        <img id="image" src="" alt="Picture" width = "100%">
                      </div>
                    </div>
                    <div class="col-md-3">
                      <div class="docs-preview clearfix">
                        <div class="img-preview preview-lg"></div>
                        <div class="img-preview preview-md"></div>
                        <div class="img-preview preview-sm"></div>
                        <div class="img-preview preview-xs"></div>
                      </div>

                      <div class="docs-data">
                        <div class="input-group input-group-sm">
                          <label class="input-group-addon" for="dataX">X</label>
                          <input type="text" class="form-control" id="dataX" placeholder="x">
                          <span class="input-group-addon">px</span>
                        </div>
                        <div class="input-group input-group-sm">
                          <label class="input-group-addon" for="dataY">Y</label>
                          <input type="text" class="form-control" id="dataY" placeholder="y">
                          <span class="input-group-addon">px</span>
                        </div>
                        <div class="input-group input-group-sm">
                          <label class="input-group-addon" for="dataWidth">Width</label>
                          <input type="text" class="form-control" id="dataWidth" placeholder="width">
                          <span class="input-group-addon">px</span>
                        </div>
                        <div class="input-group input-group-sm">
                          <label class="input-group-addon" for="dataHeight">Height</label>
                          <input type="text" class="form-control" id="dataHeight" placeholder="height">
                          <span class="input-group-addon">px</span>
                        </div>
                        <div class="input-group input-group-sm">
                          <label class="input-group-addon" for="dataRotate">Rotate</label>
                          <input type="text" class="form-control" id="dataRotate" placeholder="rotate">
                          <span class="input-group-addon">deg</span>
                        </div>
                        <div class="input-group input-group-sm">
                          <label class="input-group-addon" for="dataScaleX">ScaleX</label>
                          <input type="text" class="form-control" id="dataScaleX" placeholder="scaleX">
                        </div>
                        <div class="input-group input-group-sm">
                          <label class="input-group-addon" for="dataScaleY">ScaleY</label>
                          <input type="text" class="form-control" id="dataScaleY" placeholder="scaleY">
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-md-9 docs-buttons">
                      <!-- <h3 class="page-header">Toolbar:</h3> -->
                      <div class="btn-group">
                        <button type="button" class="btn btn-primary" data-method="setDragMode" data-option="move" title="Move">
                          <span class="docs-tooltip" data-toggle="tooltip" title="$().cropper(&quot;setDragMode&quot;, &quot;move&quot;)">
                            <span class="fa fa-arrows"></span>
                          </span>
                        </button>
                        <button type="button" class="btn btn-primary" data-method="setDragMode" data-option="crop" title="Crop">
                          <span class="docs-tooltip" data-toggle="tooltip" title="$().cropper(&quot;setDragMode&quot;, &quot;crop&quot;)">
                            <span class="fa fa-crop"></span>
                          </span>
                        </button>
                      </div>

                      <div class="btn-group">
                        <button type="button" class="btn btn-primary" data-method="zoom" data-option="0.1" title="Zoom In">
                          <span class="docs-tooltip" data-toggle="tooltip" title="$().cropper(&quot;zoom&quot;, 0.1)">
                            <span class="fa fa-search-plus"></span>
                          </span>
                        </button>
                        <button type="button" class="btn btn-primary" data-method="zoom" data-option="-0.1" title="Zoom Out">
                          <span class="docs-tooltip" data-toggle="tooltip" title="$().cropper(&quot;zoom&quot;, -0.1)">
                            <span class="fa fa-search-minus"></span>
                          </span>
                        </button>
                      </div>

                      <div class="btn-group">
                        <button type="button" class="btn btn-primary" data-method="move" data-option="-10" data-second-option="0" title="Move Left">
                          <span class="docs-tooltip" data-toggle="tooltip" title="$().cropper(&quot;move&quot;, -10, 0)">
                            <span class="fa fa-arrow-left"></span>
                          </span>
                        </button>
                        <button type="button" class="btn btn-primary" data-method="move" data-option="10" data-second-option="0" title="Move Right">
                          <span class="docs-tooltip" data-toggle="tooltip" title="$().cropper(&quot;move&quot;, 10, 0)">
                            <span class="fa fa-arrow-right"></span>
                          </span>
                        </button>
                        <button type="button" class="btn btn-primary" data-method="move" data-option="0" data-second-option="-10" title="Move Up">
                          <span class="docs-tooltip" data-toggle="tooltip" title="$().cropper(&quot;move&quot;, 0, -10)">
                            <span class="fa fa-arrow-up"></span>
                          </span>
                        </button>
                        <button type="button" class="btn btn-primary" data-method="move" data-option="0" data-second-option="10" title="Move Down">
                          <span class="docs-tooltip" data-toggle="tooltip" title="$().cropper(&quot;move&quot;, 0, 10)">
                            <span class="fa fa-arrow-down"></span>
                          </span>
                        </button>
                      </div>

                      <div class="btn-group">
                        <button type="button" class="btn btn-primary" data-method="rotate" data-option="-45" title="Rotate Left">
                          <span class="docs-tooltip" data-toggle="tooltip" title="$().cropper(&quot;rotate&quot;, -45)">
                            <span class="fa fa-rotate-left"></span>
                          </span>
                        </button>
                        <button type="button" class="btn btn-primary" data-method="rotate" data-option="45" title="Rotate Right">
                          <span class="docs-tooltip" data-toggle="tooltip" title="$().cropper(&quot;rotate&quot;, 45)">
                            <span class="fa fa-rotate-right"></span>
                          </span>
                        </button>
                      </div>

                      <div class="btn-group">
                        <button type="button" class="btn btn-primary" data-method="scaleX" data-option="-1" title="Flip Horizontal">
                          <span class="docs-tooltip" data-toggle="tooltip" title="$().cropper(&quot;scaleX&quot;, -1)">
                            <span class="fa fa-arrows-h"></span>
                          </span>
                        </button>
                        <button type="button" class="btn btn-primary" data-method="scaleY" data-option="-1" title="Flip Vertical">
                          <span class="docs-tooltip" data-toggle="tooltip" title="$().cropper(&quot;scaleY&quot;, -1)">
                            <span class="fa fa-arrows-v"></span>
                          </span>
                        </button>
                      </div>

                      <div class="btn-group">
                        <button type="button" class="btn btn-primary" data-method="crop" title="Crop">
                          <span class="docs-tooltip" data-toggle="tooltip" title="$().cropper(&quot;crop&quot;)">
                            <span class="fa fa-check"></span>
                          </span>
                        </button>
                        <button type="button" class="btn btn-primary" data-method="clear" title="Clear">
                          <span class="docs-tooltip" data-toggle="tooltip" title="$().cropper(&quot;clear&quot;)">
                            <span class="fa fa-remove"></span>
                          </span>
                        </button>
                      </div>

                      <div class="btn-group">
                        <button type="button" class="btn btn-primary" data-method="disable" title="Disable">
                          <span class="docs-tooltip" data-toggle="tooltip" title="$().cropper(&quot;disable&quot;)">
                            <span class="fa fa-lock"></span>
                          </span>
                        </button>
                        <button type="button" class="btn btn-primary" data-method="enable" title="Enable">
                          <span class="docs-tooltip" data-toggle="tooltip" title="$().cropper(&quot;enable&quot;)">
                            <span class="fa fa-unlock"></span>
                          </span>
                        </button>
                      </div>

                      <div class="btn-group">
                        <button type="button" class="btn btn-primary" data-method="reset" title="Reset">
                          <span class="docs-tooltip" data-toggle="tooltip" title="$().cropper(&quot;reset&quot;)">
                            <span class="fa fa-refresh"></span>
                          </span>
                        </button>
                        <label class="btn btn-primary btn-upload" for="inputImage" title="Upload image file">
                          <input type="file" class="sr-only" id="inputImage" name="file" accept="image/*">
                          <span class="docs-tooltip" data-toggle="tooltip" title="Import image with Blob URLs">
                            <span class="fa fa-upload"></span>
                          </span>
                        </label>
                        <button type="button" class="btn btn-primary" data-method="destroy" title="Destroy">
                          <span class="docs-tooltip" data-toggle="tooltip" title="$().cropper(&quot;destroy&quot;)">
                            <span class="fa fa-power-off"></span>
                          </span>
                        </button>
                      </div>

                      <div class="btn-group btn-group-crop">
                        <button type="button" class="btn btn-primary" data-method="getCroppedCanvas">
                          <span class="docs-tooltip" data-toggle="tooltip" title="$().cropper(&quot;getCroppedCanvas&quot;)">
                            Get Cropped Canvas
                          </span>
                        </button>
                        <button type="button" class="btn btn-primary" data-method="getCroppedSave">
                          <span class="docs-tooltip" data-toggle="tooltip" title="$().cropper(&quot;getCroppedSave&quot;)">
                            Сохранить
                          </span>
                        </button>
                        <button type="button" class="btn btn-primary" data-method="getCroppedCanvas" data-option="{ &quot;width&quot;: 160, &quot;height&quot;: 90 }">
                          <span class="docs-tooltip" data-toggle="tooltip" title="$().cropper(&quot;getCroppedCanvas&quot;, { width: 160, height: 90 })">
                            160&times;90
                          </span>
                        </button>
                        <button type="button" class="btn btn-primary" data-method="getCroppedCanvas" data-option="{ &quot;width&quot;: 320, &quot;height&quot;: 180 }">
                          <span class="docs-tooltip" data-toggle="tooltip" title="$().cropper(&quot;getCroppedCanvas&quot;, { width: 320, height: 180 })">
                            320&times;180
                          </span>
                        </button>
                      </div>

                      <!-- Show the cropped image in modal -->
                      <div class="modal fade docs-cropped" id="getCroppedCanvasModal" aria-hidden="true" aria-labelledby="getCroppedCanvasTitle" role="dialog" tabindex="-1">
                        <div class="modal-dialog">
                          <div class="modal-content">
                            <div class="modal-header">
                              <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                              <h4 class="modal-title" id="getCroppedCanvasTitle">Cropped</h4>
                            </div>
                            <div class="modal-body"></div>
                            <div class="modal-footer">
                              <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                              <a class="btn btn-primary" id="download" href="javascript:void(0);" download="cropped.png">Download</a>
                            </div>
                          </div>
                        </div>
                      </div><!-- /.modal -->

                      <button type="button" class="btn btn-primary" data-method="getData" data-option data-target="#putData">
                        <span class="docs-tooltip" data-toggle="tooltip" title="$().cropper(&quot;getData&quot;)">
                          Get Data
                        </span>
                      </button>
                      <button type="button" class="btn btn-primary" data-method="setData" data-target="#putData">
                        <span class="docs-tooltip" data-toggle="tooltip" title="$().cropper(&quot;setData&quot;, data)">
                          Set Data
                        </span>
                      </button>
                      <button type="button" class="btn btn-primary" data-method="getContainerData" data-option data-target="#putData">
                        <span class="docs-tooltip" data-toggle="tooltip" title="$().cropper(&quot;getContainerData&quot;)">
                          Get Container Data
                        </span>
                      </button>
                      <button type="button" class="btn btn-primary" data-method="getImageData" data-option data-target="#putData">
                        <span class="docs-tooltip" data-toggle="tooltip" title="$().cropper(&quot;getImageData&quot;)">
                          Get Image Data
                        </span>
                      </button>
                      <button type="button" class="btn btn-primary" data-method="getCanvasData" data-option data-target="#putData">
                        <span class="docs-tooltip" data-toggle="tooltip" title="$().cropper(&quot;getCanvasData&quot;)">
                          Get Canvas Data
                        </span>
                      </button>
                      <button type="button" class="btn btn-primary" data-method="setCanvasData" data-target="#putData">
                        <span class="docs-tooltip" data-toggle="tooltip" title="$().cropper(&quot;setCanvasData&quot;, data)">
                          Set Canvas Data
                        </span>
                      </button>
                      <button type="button" class="btn btn-primary" data-method="getCropBoxData" data-option data-target="#putData">
                        <span class="docs-tooltip" data-toggle="tooltip" title="$().cropper(&quot;getCropBoxData&quot;)">
                          Get Crop Box Data
                        </span>
                      </button>
                      <button type="button" class="btn btn-primary" data-method="setCropBoxData" data-target="#putData">
                        <span class="docs-tooltip" data-toggle="tooltip" title="$().cropper(&quot;setCropBoxData&quot;, data)">
                          Set Crop Box Data
                        </span>
                      </button>
                      <button type="button" class="btn btn-primary" data-method="moveTo" data-option="0">
                        <span class="docs-tooltip" data-toggle="tooltip" title="cropper.moveTo(0)">
                          0,0
                        </span>
                      </button>
                      <button type="button" class="btn btn-primary" data-method="zoomTo" data-option="1">
                        <span class="docs-tooltip" data-toggle="tooltip" title="cropper.zoomTo(1)">
                          100%
                        </span>
                      </button>
                      <button type="button" class="btn btn-primary" data-method="rotateTo" data-option="180">
                        <span class="docs-tooltip" data-toggle="tooltip" title="cropper.rotateTo(180)">
                          180°
                        </span>
                      </button>
                      <input type="text" class="form-control" id="putData" placeholder="Get data to here or set data with this value">
                    </div><!-- /.docs-buttons -->

                    <div class="col-md-3 docs-toggles">
                      <!-- <h3 class="page-header">Toggles:</h3> -->
                      <div class="btn-group btn-group-justified" data-toggle="buttons">
                        <label class="btn btn-primary active">
                          <input type="radio" class="sr-only" id="aspectRatio0" name="aspectRatio" value="1.7777777777777777">
                          <span class="docs-tooltip" data-toggle="tooltip" title="aspectRatio: 16 / 9">
                            16:9
                          </span>
                        </label>
                        <label class="btn btn-primary">
                          <input type="radio" class="sr-only" id="aspectRatio1" name="aspectRatio" value="1.3333333333333333">
                          <span class="docs-tooltip" data-toggle="tooltip" title="aspectRatio: 4 / 3">
                            4:3
                          </span>
                        </label>
                        <label class="btn btn-primary">
                          <input type="radio" class="sr-only" id="aspectRatio2" name="aspectRatio" value="1">
                          <span class="docs-tooltip" data-toggle="tooltip" title="aspectRatio: 1 / 1">
                            1:1
                          </span>
                        </label>
                        <label class="btn btn-primary">
                          <input type="radio" class="sr-only" id="aspectRatio3" name="aspectRatio" value="0.6666666666666666">
                          <span class="docs-tooltip" data-toggle="tooltip" title="aspectRatio: 2 / 3">
                            2:3
                          </span>
                        </label>
                        <label class="btn btn-primary">
                          <input type="radio" class="sr-only" id="aspectRatio4" name="aspectRatio" value="NaN">
                          <span class="docs-tooltip" data-toggle="tooltip" title="aspectRatio: NaN">
                            Free
                          </span>
                        </label>
                      </div>

                      <div class="btn-group btn-group-justified" data-toggle="buttons">
                        <label class="btn btn-primary active">
                          <input type="radio" class="sr-only" id="viewMode0" name="viewMode" value="0" checked>
                          <span class="docs-tooltip" data-toggle="tooltip" title="View Mode 0">
                            VM0
                          </span>
                        </label>
                        <label class="btn btn-primary">
                          <input type="radio" class="sr-only" id="viewMode1" name="viewMode" value="1">
                          <span class="docs-tooltip" data-toggle="tooltip" title="View Mode 1">
                            VM1
                          </span>
                        </label>
                        <label class="btn btn-primary">
                          <input type="radio" class="sr-only" id="viewMode2" name="viewMode" value="2">
                          <span class="docs-tooltip" data-toggle="tooltip" title="View Mode 2">
                            VM2
                          </span>
                        </label>
                        <label class="btn btn-primary">
                          <input type="radio" class="sr-only" id="viewMode3" name="viewMode" value="3">
                          <span class="docs-tooltip" data-toggle="tooltip" title="View Mode 3">
                            VM3
                          </span>
                        </label>
                      </div>

                      <div class="dropdown dropup docs-options">
                        <button type="button" class="btn btn-primary btn-block dropdown-toggle" id="toggleOptions" data-toggle="dropdown" aria-expanded="true">
                          Toggle Options
                          <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="toggleOptions" role="menu">
                          <li role="presentation">
                            <label class="checkbox-inline">
                              <input type="checkbox" name="responsive" checked>
                              responsive
                            </label>
                          </li>
                          <li role="presentation">
                            <label class="checkbox-inline">
                              <input type="checkbox" name="restore" checked>
                              restore
                            </label>
                          </li>
                          <li role="presentation">
                            <label class="checkbox-inline">
                              <input type="checkbox" name="checkCrossOrigin" checked>
                              checkCrossOrigin
                            </label>
                          </li>
                          <li role="presentation">
                            <label class="checkbox-inline">
                              <input type="checkbox" name="checkOrientation" checked>
                              checkOrientation
                            </label>
                          </li>

                          <li role="presentation">
                            <label class="checkbox-inline">
                              <input type="checkbox" name="modal" checked>
                              modal
                            </label>
                          </li>
                          <li role="presentation">
                            <label class="checkbox-inline">
                              <input type="checkbox" name="guides" checked>
                              guides
                            </label>
                          </li>
                          <li role="presentation">
                            <label class="checkbox-inline">
                              <input type="checkbox" name="center" checked>
                              center
                            </label>
                          </li>
                          <li role="presentation">
                            <label class="checkbox-inline">
                              <input type="checkbox" name="highlight" checked>
                              highlight
                            </label>
                          </li>
                          <li role="presentation">
                            <label class="checkbox-inline">
                              <input type="checkbox" name="background" checked>
                              background
                            </label>
                          </li>

                          <li role="presentation">
                            <label class="checkbox-inline">
                              <input type="checkbox" name="autoCrop" checked>
                              autoCrop
                            </label>
                          </li>
                          <li role="presentation">
                            <label class="checkbox-inline">
                              <input type="checkbox" name="movable" checked>
                              movable
                            </label>
                          </li>
                          <li role="presentation">
                            <label class="checkbox-inline">
                              <input type="checkbox" name="rotatable" checked>
                              rotatable
                            </label>
                          </li>
                          <li role="presentation">
                            <label class="checkbox-inline">
                              <input type="checkbox" name="scalable" checked>
                              scalable
                            </label>
                          </li>
                          <li role="presentation">
                            <label class="checkbox-inline">
                              <input type="checkbox" name="zoomable" checked>
                              zoomable
                            </label>
                          </li>
                          <li role="presentation">
                            <label class="checkbox-inline">
                              <input type="checkbox" name="zoomOnTouch" checked>
                              zoomOnTouch
                            </label>
                          </li>
                          <li role="presentation">
                            <label class="checkbox-inline">
                              <input type="checkbox" name="zoomOnWheel" checked>
                              zoomOnWheel
                            </label>
                          </li>
                          <li role="presentation">
                            <label class="checkbox-inline">
                              <input type="checkbox" name="cropBoxMovable" checked>
                              cropBoxMovable
                            </label>
                          </li>
                          <li role="presentation">
                            <label class="checkbox-inline">
                              <input type="checkbox" name="cropBoxResizable" checked>
                              cropBoxResizable
                            </label>
                          </li>
                          <li role="presentation">
                            <label class="checkbox-inline">
                              <input type="checkbox" name="toggleDragModeOnDblclick" checked>
                              toggleDragModeOnDblclick
                            </label>
                          </li>
                        </ul>
                      </div><!-- /.dropdown -->

                      <a class="btn btn-default btn-block" data-toggle="tooltip" href="https://fengyuanchen.github.io/cropperjs" title="Cropper without jQuery">Cropper.js</a>

                    </div><!-- /.docs-toggles -->
                  </div>
                </div>
                <!-- /image cropping -->
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-default" data-dismiss="img_modal">Close</button>
            </div>
          </div>
        </div>
      </div>
    </div>
    ';
    
    return $output;
  }
  
  
}
?>