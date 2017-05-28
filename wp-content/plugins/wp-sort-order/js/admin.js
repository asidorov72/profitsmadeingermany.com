// JavaScript Document
	function op1_func($){
		var op1 = true;
		$.each($('.wpso .options1 input[type="checkbox"]'), function(){
			if(!$(this).prop( "checked" ))
			op1 = false;
		});
		if(op1){
			$('#wpso_allcheck_objects').attr("checked", "checked");
			$('#wpso_allcheck_objects').parent().addClass('check');
		}else{
			$('#wpso_allcheck_objects').parent().removeClass('check');
		}
	}
	
	function op2_func($){
		var op2 = true;
		$.each($('.wpso .options2 input[type="checkbox"]'), function(){
			if(!$(this).prop( "checked" ))
			op2 = false;
		});
		
		if(op2){
			$('#wpso_allcheck_tags').attr("checked", "checked");
			$('#wpso_allcheck_tags').parent().addClass('check');
		}else{
			$('#wpso_allcheck_tags').parent().removeClass('check');
		}
	}
	jQuery(document).ready(function($){
		
		$('.wpso label.clickable').on('click', function(e){
		
			if(e.timeStamp!=0){
				if($(this).hasClass('check'))
				$(this).removeClass('check');
				else
				$(this).addClass('check');
			}
	
			
		});
		
		$.each($('.wp-submenu li a'), function(){
			if($(this).attr('href')=='options-general.php?page=wpso-settings'){
				$(this).parent().addClass('wpso_menu');
			}
		});
		
	
		
		op1_func($);
		op2_func($);
		
		$('.wpso .options1 input[type="checkbox"]').on('click', function(){
			op1_func($);
		});
		
		$('.wpso .options2 input[type="checkbox"]').on('click', function(){
			op2_func($);
			
		});	
		
		$('small.premium').on('click', function(){
			window.open($('a.premium').attr('href'));
			
		});
	});