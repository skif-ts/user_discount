function reloadBasket(){
    $.ajax({
        method: "GET",
        success: function(data){
            $('.cart_wrapper').html($(data).find('.cart_wrapper').html());
            initSelect();
            $('.cart_wrapper').removeClass('loading');
            $('.js-favorite').on('click', function() {
                // Нужен запрос на добавление товара в избранное
                $(this).toggleClass('active');
            });
        }
    });
}
function initSelect(){
    if ($('.js-select').length) {
        $('.js-select').each(function() {
            var select = $(this);
            select.select2({
                placeholder: select.data('placeholder'),
                minimumResultsForSearch: -1,
                width: 'resolve',
                dropdownParent: select.closest('.main-select')
            });

            select.on('select2:open', function (e) {
                e.preventDefault();
                Scrollbar.init(this.parentElement.querySelector('.select2-results'), {
                    damping: 0.05,
                });
            });

            select.on('select2:close', function (e) {
                e.preventDefault();
                Scrollbar.destroy(this.parentElement.querySelector('.select2-results'));
            });
        });
    }
}

$(document).ready(function(){
    //window.deletingFromBasket = [];
	
	/*$('body').on('click', '.return-product', function(){
        var id =$(this).closest('.delet_product').closest('.cart_product').attr('data-id');
        clearTimeout(window.deletingFromBasket[id]);
        $(this).closest('.delet_product').siblings('.cart_item_wrapper').css('display', 'flex');
        $(this).closest('.delet_product').css('display', 'none');
    });*/
	
	
    $(document).on("click", ".get_discount .submit_but", function (e) {
		$(".get_discount .ok_text").text("");
		$(".get_discount .err_text").text("");
		
		var query = {
			c: 'ts:user_discount',
			action: 'get_discount',
			mode: 'class'
		};
		 
		var data = {
			//param1: 'eee',
			SITE_ID: 's1',
			sessid: BX.message('bitrix_sessid')
		};
		 
		var request = $.ajax({
			url: '/bitrix/services/main/ajax.php?' + $.param(query, true),
			method: 'POST',
			data: data
		});
		 
		request.done(function (response) {
			console.log(response);
			if(response.status == "success"){
				$(".get_discount .ok_text").text("Скидка: " + response.data.discount + "%, код для получения скидки: " + response.data.code);
			}else{
				$(".get_discount .err_text").text("Ошибка при получении скидки");
			}
		});		
		
        e.preventDefault();

    });
	
    $(document).on("click", ".check_discount .submit_but", function (e) {
		$(".check_discount .ok_text").text("");
		$(".check_discount .err_text").text("");
		
		var query = {
			c: 'ts:user_discount',
			action: 'check_discount',
			mode: 'class'
		};
		 
		var data = {
			coupon: $(".check_discount .coupon").val(),
			SITE_ID: 's1',
			sessid: BX.message('bitrix_sessid')
		};
		 
		var request = $.ajax({
			url: '/bitrix/services/main/ajax.php?' + $.param(query, true),
			method: 'POST',
			data: data
		});
		 
		request.done(function (response) {
			console.log(response);
			if(response.status == "success"){
				if(response.data.err_code == 0){
					$(".check_discount .ok_text").text("Ваша скидка: " + response.data.discount + "%");
				}else{
					$(".check_discount .err_text").text("Скидка недоступна");
				}
				
			}else{
				$(".check_discount .err_text").text("Ошибка");
			}
		});		
		
        e.preventDefault();

    });	

})
