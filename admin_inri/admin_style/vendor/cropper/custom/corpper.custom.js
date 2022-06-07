
$(document).ready(function() {
    $('#edit_picture_btn').on('click', function () {
      var src = $('#imageimg').attr("src");
      $('#image').attr("src", src);
    });
    
    $('#edit_orig_picture_btn').on('click', function () {
      
      var src = $('#orig_imageimg_path').data("orig_path");
      // alert("src = " + src);
      $('#image').attr("src", src);
    })
    
    $('#img_modal').on('shown.bs.modal', function () {
      if(!$init_cropper){
        $init_cropper = true;
        init_cropper();  
      }else{
        $image.cropper('reset');
      }
      
    }).on('hidden.bs.modal', function () {
      $image.cropper('destroy');
    });
    
    $('#setCroppData').on('click', function (event) {
      var obj = {
        x: parseFloat ( $('#dataX').val() ),
        y: parseFloat( $('#dataY').val() ),
        width: parseFloat( $('#dataWidth').val() ),
        height: parseFloat( $('#dataHeight').val() ),
        rotate: parseFloat( $('#dataRotate').val() ),
        scaleX: parseFloat( $('#dataScaleX').val() ),
        scaleY: parseFloat( $('#dataScaleY').val() )
      }
      $image.cropper("setData", obj);
      //$('#putCroppData').val( JSON.stringify(obj));
      //console.log($image.cropper("setData", obj));
      
    });
  
    /* CROPPER */
		var $image = $('#image');
    var $init_cropper = false;
    
		function init_cropper() {
			if( typeof ($.fn.cropper) === 'undefined'){ return; }
			console.log('init_cropper');
			
			
			var $download = $('#download');
			var $dataX = $('#dataX');
			var $dataY = $('#dataY');
			var $dataHeight = $('#dataHeight');
			var $dataWidth = $('#dataWidth');
			var $dataRotate = $('#dataRotate');
			var $dataScaleX = $('#dataScaleX');
			var $dataScaleY = $('#dataScaleY');
      var paramobj = {
        x: $('#dataX').val(),
        y: $('#dataY').val(),
        width: $('#dataWidth').val(),
        height: $('#dataHeight').val(),
        rotate: $('#dataRotate').val(),
        scaleX: $('#dataScaleX').val(),
        scaleY: $('#dataScaleY').val()
      }
			var options = {
				  aspectRatio: 16 / 9,
          viewMode: 2,
				  preview: '.img-preview',
				  crop: function (e) {
					$dataX.val(Math.round(e.x));
					$dataY.val(Math.round(e.y));
					$dataHeight.val(Math.round(e.height));
					$dataWidth.val(Math.round(e.width));
					$dataRotate.val(e.rotate);
					$dataScaleX.val(e.scaleX);
					$dataScaleY.val(e.scaleY);
				  }
				};


			// Tooltip
			$('[data-toggle="tooltip"]').tooltip();


			// Cropper
			$image.on({
			  'build.cropper': function (e) {
				console.log(e.type);
			  },
			  'built.cropper': function (e) {
				console.log(e.type);
			  },
			  'cropstart.cropper': function (e) {
				console.log(e.type, e.action);
			  },
			  'cropmove.cropper': function (e) {
				console.log(e.type, e.action);
			  },
			  'cropend.cropper': function (e) {
				console.log(e.type, e.action);
			  },
			  'crop.cropper': function (e) {
				console.log(e.type, e.x, e.y, e.width, e.height, e.rotate, e.scaleX, e.scaleY);
			  },
			  'zoom.cropper': function (e) {
				console.log(e.type, e.ratio);
			  }
			}).cropper(options);


			// Buttons
			if (!$.isFunction(document.createElement('canvas').getContext)) {
			  $('button[data-method="getCroppedCanvas"]').prop('disabled', true);
			}

			if (typeof document.createElement('cropper').style.transition === 'undefined') {
			  $('button[data-method="rotate"]').prop('disabled', true);
			  $('button[data-method="scale"]').prop('disabled', true);
			}


			// Download
			if (typeof $download[0].download === 'undefined') {
			  $download.addClass('disabled');
			}


			// Options
			$('.docs-toggles').on('change', 'input', function () {
			  var $this = $(this);
			  var name = $this.attr('name');
			  var type = $this.prop('type');
			  var cropBoxData;
			  var canvasData;

			  if (!$image.data('cropper')) {
				return;
			  }

			  if (type === 'checkbox') {
				options[name] = $this.prop('checked');
				cropBoxData = $image.cropper('getCropBoxData');
				canvasData = $image.cropper('getCanvasData');

				options.built = function () {
				  $image.cropper('setCropBoxData', cropBoxData);
				  $image.cropper('setCanvasData', canvasData);
				};
			  } else if (type === 'radio') {
				options[name] = $this.val();
			  }

			  $image.cropper('destroy').cropper(options);
			});


			// Methods
			$('.docs-buttons').on('click', '[data-method]', function () {
			  var $this = $(this);
			  var data = $this.data();
			  var $target;
			  var result;

			  if ($this.prop('disabled') || $this.hasClass('disabled')) {
				return;
			  }

			  if ($image.data('cropper') && data.method) {
				data = $.extend({}, data); // Clone a new one

				if (typeof data.target !== 'undefined') {
				  $target = $(data.target);

				  if (typeof data.option === 'undefined') {
					try {
					  data.option = JSON.parse($target.val());
					} catch (e) {
					  console.log(e.message);
					}
				  }
				}
        
				result = $image.cropper(data.method, data.option, data.secondOption);

				switch (data.method) {
				  case 'scaleX':
				  case 'scaleY':
					$(this).data('option', -data.option);
					break;

				  case 'getCroppedCanvas':
          
					if (result) {
            
            // alert("tecn1" + result.toBlob(blob));
            
            var iimg    = result.toDataURL("image/png");
            $('#imageimg').attr('src', iimg);
            
            // $('textarea[name="corp_imageimg"]').html(iimg);
            // $('#img_modal').modal('hide');
            
            result.toBlob(function (blob) {
              var imgCorpScriptPath = $('#imgCorpScriptPath').val();
              var formData = new FormData();

              formData.append('croppedImage', blob);

              // Use `jQuery.ajax` method
              $.ajax(imgCorpScriptPath, {
                method: "POST",
                data: formData,
                processData: false,
                contentType: false,
                success: function (date) {
                  console.log('Upload success');
                  if(date == 'ok'){
                    // $('#img_modal').modal('hide');
                    // location.reload()
                    $('#submit').click(); 
                  }else{
                    alert(date);  
                  }
                  
                },
                error: function () {
                  console.log('Upload error');
                }
              });
            });
            
					  // Bootstrap's Modal
            /*
					  $('#getCroppedCanvasModal').modal().find('.modal-body').html(result);
            
					  if (!$download.hasClass('disabled')) {
						$download.attr('href', result.toDataURL());
            
					  }*/
					}

					break;
          
          case 'getCroppedSave':
            //alert("tecn1" + result.toDataURL());
   					if (result) {
            
					  }

					break;
				}

				if ($.isPlainObject(result) && $target) {
				  try {
					$target.val(JSON.stringify(result));
				  } catch (e) {
					console.log(e.message);
				  }
				}

			  }
			});

			// Keyboard
			$(document.body).on('keydown', function (e) {
			  if (!$image.data('cropper') || this.scrollTop > 300) {
				return;
			  }

			  switch (e.which) {
				case 37:
				  e.preventDefault();
				  $image.cropper('move', -1, 0);
				  break;

				case 38:
				  e.preventDefault();
				  $image.cropper('move', 0, -1);
				  break;

				case 39:
				  e.preventDefault();
				  $image.cropper('move', 1, 0);
				  break;

				case 40:
				  e.preventDefault();
				  $image.cropper('move', 0, 1);
				  break;
			  }
			});

			// Import image
			var $inputImage = $('#inputImage');
			var URL = window.URL || window.webkitURL;
			var blobURL;

			if (URL) {
			  $inputImage.change(function () {
				var files = this.files;
				var file;

				if (!$image.data('cropper')) {
				  return;
				}

				if (files && files.length) {
				  file = files[0];

				  if (/^image\/\w+$/.test(file.type)) {
					blobURL = URL.createObjectURL(file);
					$image.one('built.cropper', function () {

					  // Revoke when load complete
					  URL.revokeObjectURL(blobURL);
					}).cropper('reset').cropper('replace', blobURL);
					$inputImage.val('');
				  } else {
					window.alert('Please choose an image file.');
				  }
				}
			  });
			} else {
			  $inputImage.prop('disabled', true).parent().addClass('disabled');
			}
			
			
		};
		
		/* CROPPER --- end */  
    

  });