<div class="modal fade" id="order_id" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header-view" >
<!--                <button type="button" class="close" data-dismiss="modal"><font class="white">&times;</font></<button>-->
                <h4 class="viewdetails_details"><span class="fa fa-briefcase"></span>&nbsp;{{ PopUpheadingMain($result) }}</h4>
            </div>
            <div class="modal-body-view">
                <div class="alert-new-success" id="successMessage_order" style="display:none;">
<!--                    <button type="button" class="close" data-dismiss="alert">×</button>	-->
                    <strong id="success_order"></strong>
                </div>
                <ul class="list-group-order-main">
                    <li class="order-sub"><a href="javascript:void(0);">Bus Type</a>
                        <a href="javascript:void(0);">Order Number</a>
                        <a href="javascript:void(0);">Service Name</a>
                    </li>  </ul>
                <ul class="list-group-order" id="order_list">
                    <?php //echo orderList('services', 'id', 'bus_type_id', 'order_number', 'abbreviation'); ?>
                </ul>
         </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-dismiss="modal" onclick="Close()">Close</button>
                </div>
            </div>
        </div>

    </div>
</div>
<div class="modal fade" id="view_detail" role="dialog">
 </div>
<script>
    
   function orderList(order_id,order_list)
   {
   var urldata=   '/services/' + order_list;
    
    $.ajax({
		type: "GET",
		url: urldata,
		cache: false,
		success: function(data){
                  $("#" + order_id).modal('show');
                  $("#"+order_list).html(data);
		}
	});
  
   }
   
   function viewDetails(id,view_detail)
   {
   var urldata=   '/services/' + view_detail + '/' +id;
    
    $.ajax({
		type: "GET",
		url: urldata,
		cache: false,
		success: function(data){
                   // alert(data);
                 $("#" + view_detail).modal('show');
                  $("#"+view_detail).html(data);
		}
	});
  
   }
    
    
 $(document).ready(function(){	
	$("ul.list-group-order").sortable({		
		update: function( event, ui ) {
			updateOrder();
		}
	});  
      
});

function updateOrder() {	
	var item_order = new Array();
	$('ul.list-group-order li').each(function() {
          var text= $(this).attr("id");
          var id = text.replace('order','');
          item_order.push(id);
	});
    
	var order_string = item_order;
	$.ajax({
		type: "GET",
		url: "/services/sort_order/"+order_string,
		data: order_string,
		cache: false,
		success: function(data){
                  $("#successMessage_order").show();
                  $("#success_order").html("Order Number Updated successfully.");
                  $("#example1").html(data);
                  setTimeout(function() {
                  $('#successMessage_order').fadeOut('fast');
        }, 5000); // <-- time in milliseconds 
		}
	});
}  
</script>