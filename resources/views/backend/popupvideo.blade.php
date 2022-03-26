
        <div class="modal fade" id="myModal1" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
          <div class="modal-dialog" role="document">
            <div class="modal-content">
              <div class="modal-body">
               <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>        
        			<div class="embed-responsive embed-responsive-16by9">
        			  <iframe class="embed-responsive-item" src="" id="video"  allowscriptaccess="always" allow="autoplay"></iframe>
        			</div>
        	    </div>

        	    <div class="form-check  py-2 text-center">
        		  <input class="form-check-input" type="checkbox" value="" id="defaultCheck1" onclick="addCookie()">
        		  <label class="form-check-label" for="defaultCheck1">
        		    Nu mai afisa
        		  </label>
        		</div>
        			    
            </div>
          </div>
        </div> 


@push('styles')
   <style>
   body {margin:2rem;}

			.modal-dialog {
			      max-width: 900px;
			      margin: 50px auto;
			  }

			.modal-body {
			  position:relative;
			  padding:0px;
			}
			.close {
			  position:absolute;
			  right:-30px;
			  top:0;
			  z-index:999;
			  font-size:2rem;
			  font-weight: normal;
			  color:#fff;
			  opacity:1;
			}
   </style>
@endpush

@section('js-script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-cookie/1.4.1/jquery.cookie.js"></script> 
<script>
$(document).ready(function() {
			var $videoSrc; 
			$videoSrc = "https://www.youtube.com/embed/_8Y7fByhOdQ";
			$('.video-btn').click(function() {
			    // $videoSrc = $(this).data( "src" );
		  		$('#myModal1').modal("show");
			});
			if($.cookie('PopupVideo') == undefined)	
			{
		 	 	$('#myModal1').modal("show");
			}    
			$('#myModal1').on('shown.bs.modal', function (e) {
			// $("#video").attr('src',$videoSrc + "?autoplay=1&amp;modestbranding=1&amp;showinfo=0" ); 
			$("#video").attr('src',$videoSrc + "?autoplay=1" ); 
			})
			$('#myModal1').on('hide.bs.modal', function (e) {
			    $("#video").attr('src',$videoSrc); 
			}) 

});

      let addCookie=()=>{ $.cookie("PopupVideo", "Video_Tutorial", { expires: 3000, path: '/' });} 
      let removeCookie=()=>{ $.removeCookie("PopupVideo","Video_Tutorial"); } 
</script>    
@endsection    