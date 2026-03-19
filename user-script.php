    <!-- plugins:js -->
    <script src="../vendors/base/vendor.bundle.base.js"></script>
    <!-- endinject -->
    <!-- Plugin js for this page-->
    <script src="../vendors/chart.js/Chart.min.js"></script>
    <script src="../vendors/datatables.net/jquery.dataTables.js"></script>
    <script src="../vendors/datatables.net-bs4/dataTables.bootstrap4.js"></script>
    <!-- End plugin js for this page-->
    <!-- inject:js -->
    <script src="../js/off-canvas.js"></script>
    <script src="../js/hoverable-collapse.js"></script>
    <script src="../js/template.js"></script>
    <!-- endinject -->
    <!-- Custom js for this page-->
    <script src="../js/dashboard.js"></script>
    <script src="../js/data-table.js"></script>
    <script src="../js/jquery.dataTables.js"></script>
    <script src="../js/dataTables.bootstrap4.js"></script>
    <!-- End custom js for this page-->

    <!-- Page level plugins -->
    <script src="../vendors/datatables.net/jquery.dataTables.min.js"></script>
    <script src="../vendors/datatables.net-bs4/dataTables.bootstrap4.min.js"></script>

    <script type="text/javascript" src="http://cdn.datatables.net/buttons/1.3.1/js/dataTables.buttons.min.js"></script>
    <script type="text/javascript" src="http://cdn.datatables.net/buttons/1.3.1/js/buttons.bootstrap4.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script type="text/javascript" src="https://cdn.rawgit.com/bpampuch/pdfmake/0.1.27/build/pdfmake.min.js"></script>
    <script type="text/javascript" src="https://cdn.rawgit.com/bpampuch/pdfmake/0.1.27/build/vfs_fonts.js"></script>
    <script type="text/javascript" src="http://cdn.datatables.net/buttons/1.3.1/js/buttons.html5.min.js"></script>

    <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
    <script src="https://cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js"></script>
    <script src="../vendor_1/datatables/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.5.2/js/dataTables.buttons.min.js"></script>
    <script src="../vendor_1/datatables/js/buttons.bootstrap4.min.js"></script>
    <script src="../vendor_1/datatables/js/data-table.js?v=<?php echo time(); ?>"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.5.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.5.2/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.5.2/js/buttons.colVis.min.js"></script>
    <script src="https://cdn.datatables.net/rowgroup/1.0.4/js/dataTables.rowGroup.min.js"></script>
    <script src="https://cdn.datatables.net/searchpanes/1.2.0/js/dataTables.searchPanes.min.js"></script>
    <script src="https://cdn.datatables.net/select/1.3.1/js/dataTables.select.min.js"></script>
    <script src="https://cdn.datatables.net/fixedheader/3.1.5/js/dataTables.fixedHeader.min.js"></script>

    <script src="../../js/sweetalert2.all.min.js"></script>
    <!-- Optional: include a polyfill for ES6 Promises for IE11 -->
    <script src="https://cdn.jsdelivr.net/npm/promise-polyfill"></script>
    <script src="../../js/sweetalert2.min.js"></script>
    <link rel="stylesheet" href="../../css/sweetalert2.min.css">
    <script type="text/javascript" src="../../js/main.js?v=<?php echo time(); ?>"></script>
	<!--<script type="text/javascript" src="<?php latest_version('../../js/main.js'); ?>"></script>-->
	<script src="../../js/check.js"></script>
    <script type="text/javascript">
        $(".toggle-password").click(function() {

          $(this).toggleClass("fa-eye fa-eye-slash");
          var input = $($(this).attr("toggle"));
          if (input.attr("type") == "password") {
            input.attr("type", "text");
        } else {
            input.attr("type", "password");
        }
    });
</script>

<style>
    .field-icon {
      float: right;
      margin-left: -25px;
      margin-top: -25px;
      position: relative;
      z-index: 2;
      cursor:pointer;
  }

  .swal2-container {
      z-index: 100000000000; !important
  }

  .swal-overlay  
  {
    z-index: 100000000000; !important    
  }
</style>

<link href="../../dropzone/dropzone.css" type="text/css" rel="stylesheet" />
<link href="../../dropzone/min/dropzone.min.css" type="text/css" rel="stylesheet" />
<script src="../../dropzone/dropzone.js"></script>
<script src="../../dropzone/min/dropzone.min.js"></script>

<script type="text/javascript">
    $("div#myproductdropzone").dropzone({
    	init: function() {
        thisDropzone = this;
        
    this.on('thumbnail', function(file, dataUrl) {
        var thumbs = document.querySelectorAll('.dz-image');
        [].forEach.call(thumbs, function (thumb) {
            var img = thumb.querySelector('img');
            if (img) {
                img.setAttribute('width', '150px');
                img.setAttribute('height', '150px');
                img.setAttribute('style', "object-fit:cover");
            }
        });
    });

        	setTimeout(function(){
        
            var action="GET_PRODUCT_IMAGES";
            var product_id=$('#product_id').val();
            
            $.ajax({
                type:'POST',
                url:'../../action/select.php',
                data:{
                    action:action,
                    product_id:product_id,
                },

                success: function (response){
                	
            		$.each(response, function(key,value){
                 
                		var mockFile = { name: value.name, size: value.size };

                		thisDropzone.options.addedfile.call(thisDropzone, mockFile);
                		thisDropzone.options.thumbnail.call(thisDropzone, mockFile, "../../uploads/"+value.name);
   						mockFile.previewElement.classList.add('dz-complete');
                    
                    	mockFile.previewElement.addEventListener("click", function(e) {
          					var win = window.open(this.querySelectorAll('.dz-image > img')[0].src, '_blank');
          					win.focus();
        				});
                    
                    	//thisDropzone.emit("addedfile", mockFile);
         				//thisDropzone.emit("thumbnail", mockFile, "../../uploads/"+value.name);
          				//thisDropzone.emit("complete", mockFile);
                    
        var thumbs = document.querySelectorAll('.dz-image');
        [].forEach.call(thumbs, function (thumb) {
            var img = thumb.querySelector('img');
            if (img) {
                img.setAttribute('width', '150px');
                img.setAttribute('height', '150px');
                img.setAttribute('style', "object-fit:cover");
            }
        });
                 
            		});
                                    
                }

            });
            
            }, 1000);
        
    	},
        url: "../../action/save_image.php",
        addRemoveLinks: true,
        success: function (file, response) {

            obj = JSON.parse(response);
            
            var action="SAVE_PRODUCT_IMAGE";
            var product_id=$('#product_id').val();
            var picture_name = obj.filename;
            
            $.ajax({
                type:'POST',
                url:'../../action/insert.php',
                data:{
                    action:action,
                    product_id:product_id,
                    picture_name:picture_name,
                },

                success: function (response){
                    //$("#imaginary").html(response);
                }

            });

            $(file.previewTemplate).append("<span class='server_file' style='display:none'>"+obj.filename+"</span>");
            
        },
        removedfile: function (file) {
            var filename = $(file.previewTemplate).children('.server_file').text();

            var action="DELETE_PRODUCT_IMAGE";
            var picture_name = filename;
        
        	if(picture_name === ''){
            	picture_name = file.name;
            }
                    
            $.ajax({
                type:'POST',
                url:'../../action/delete.php',
                data:{
                    action:action,
                    picture_name:picture_name,
                },

                success: function (response){
                    //$("#imaginary").html(response);
                }

            });

            file.previewElement.remove();

        },
        error: function (file, response) {

        }

    });

</script>