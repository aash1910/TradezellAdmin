/***************************************************
==================== JS INDEX ======================
****************************************************
Mobile Menu Js


****************************************************/

(function ($) {
	"use strict";

	/*==========================
		Background Image
	============================*/
	$("[data-bg-image]").each(function () {
		$(this).css(
			"background-image",
			"url( " + $(this).attr("data-bg-image") + "  )"
		);
	});

	////////////////////////////////////////////////////
	// Nice Select
	$("select").niceSelect();

	////////////////////////////////////////////////////
	// Mega Menu
	if ($(".mega_menu_bars").length > 0) {
		$(".mega_menu_bars").on("click", function () {
			$(this).toggleClass("active");
			$(".mega_menus").toggleClass("open");
		});
	}

	////////////////////////////////////////////////////
	// Mobile Menu Js
	$(".primary_menu").meanmenu({
		meanMenuContainer: ".mobile_menu",
		meanScreenWidth: "767",
		meanExpand: ['<i class="fa-light fa-angle-right"></i>'],
	});

	$(".menu_bar .bars").on("click", function () {
		$(".hamburger-area").addClass("opened");
		$(".body-overlay").addClass("opened");
	});
	$(".hamburger_close_btn").on("click", function () {
		$(".hamburger-area").removeClass("opened");
		$(".body-overlay").removeClass("opened");
	});

	$(".body-overlay").on("click", function () {
		$(".hamburger-area").removeClass("opened");
		$(".body-overlay").removeClass("opened");
	});

	////////////////////////////////////////////////////
	// Featured Haors
	var swiper = new Swiper(".featured_haors_carousel", {
		effect: "coverflow",
		grabCursor: false,
		slideToClickedSlide: false,
		centeredSlides: true,
		slidesPerView: "auto",
		loop: true,
		autoplay: {
			delay: 3000,
		},
		initialSlide: 2,
		coverflowEffect: {
			rotate: 0,
			stretch: 0,
			depth: 50,
			modifier: 4,
			initialSlide: 2,
			slideShadows: false,
		},
		spaceBetween: 75,
		navigation: {
			nextEl: ".featured_haors_nav-next",
			prevEl: ".featured_haors_nav-prev",
		},
		breakpoints: {
			575: {
				spaceBetween: 25,
				coverflowEffect: {
					modifier: 2,
					initialSlide: 1,
				},
			},
			767: {
				spaceBetween: 30,
				coverflowEffect: {
					modifier: 2,
					initialSlide: 1,
				},
			},
			991: {
				spaceBetween: 30,
				coverflowEffect: {
					modifier: 2,
					initialSlide: 1,
				},
			},
		},
	});

	////////////////////////////////////////////////////
	// GAllery Slider
	$(".gallery-section .owl-carousel").owlCarousel({
		loop: true,
		center: true,
		nav: false,
		dots: false,
		items: 5,
		autoplay: true,
		autoplayHoverPause: true,
		autoplayTimeout: 3000,
		responsive: {
			0: {
				items: 1,
				center: false,
			},
			768: {
				items: 3,
			},
			992: {
				items: 3,
			},
			1200: {
				items: 5,
			},
		},
	});

	////////////////////////////////////////////////////
	// Overview Carousel
	if ($(".overview_carousel").length > 0) {
		$(".overview_carousel").owlCarousel({
			loop: true,
			items: 1,
			nav: false,
			dots: false,
			thumbs: true,
			thumbImage: true,
			thumbContainerClass: "owl-thumbs",
			thumbItemClass: "owl-thumb-item",
		});
	}

	////////////////////////////////////////////////////
	// Haor Carousel
	if ($(".haors_carousel").length > 0) {
		$(".haors_carousel").owlCarousel({
			loop: $('.haors_carousel .singele_haor').size > 4 ? true:false,
			items: 4,
			margin: 15,
			dots: false,
			nav: true,
			navText: [
				"<i class='fal fa-arrow-left'></i>",
				"<i class='fal fa-arrow-right'></i>",
			],
			responsive: {
				0: {
					items: 1,
				},
				576: {
					items: 2,
				},
				768: {
					items: 3,
				},
				992: {
					items: 4,
				},
			},
		});
	}

	////////////////////////////////////////////////////
	// Magnific Popup
	if ($(".gallery_img").length > 0) {
		$(".gallery_img").magnificPopup({
			type: "image",
			mainClass: "mfp-with-zoom",
			gallery: {
				enabled: true,
			},

			zoom: {
				enabled: true,

				duration: 300, // duration of the effect, in milliseconds
				easing: "ease-in-out", // CSS transition easing function
			},
		});
	}

	////////////////////////////////////////////////////
	// Hero Video
	// if ($(".hero-section").length > 0) {
	// 	$(".hero-section").vide(
	// 		{},
	// 		{
	// 			muted: true,
	// 			loop: true,
	// 			posterType: "jpg",
	// 			className: "",
	// 		}
	// 	);
	// }

	////////////////////////////////////////////////////
	// Explore Masonry
	$(".grid").imagesLoaded(function () {
		var $grid = $(".grid").isotope({
			layoutMode: "fitRows",
			itemSelector: ".grid-item",
			percentPosition: true,
			fitRows: {
				gutter: ".gutter-sizer",
			},
		});
	});


	$(".muteVideoBtn").click(function () {
		let video = $("#topVideo");
		if (video.prop('muted')) {
			video.prop('muted', false);
			$(".muteVideoBtnIcon").removeClass('fa-volume-mute');
		} else {
			video.prop('muted', true);
			$(".muteVideoBtnIcon").addClass('fa-volume-mute');
		}
		console.log(video.prop('muted'))
	});
	$(".playVideoBtn").click(function () {
		let video = $("#topVideo").get(0);
		if (video.paused) {
			video.play();
			$(".playVideoBtnIcon").removeClass('fa-play');
		} else {
			video.pause();
			$(".playVideoBtnIcon").addClass('fa-play');
		}
	});

	$(".fullscreenVideoBtn").click(function () {
		let video = $("#topVideo").get(0);
		if (video.requestFullscreen) {
			video.requestFullscreen();
		  } else if (video.webkitRequestFullscreen) { /* Safari */
		  	video.webkitRequestFullscreen();
		  } else if (video.msRequestFullscreen) { /* IE11 */
		  	video.msRequestFullscreen();
		  }
	});

})(jQuery);


  $(document).on("change", "#district",function(e){
    e.preventDefault();
	$("#thana").empty();
	$("#haor-list").empty();
	$("#haor").val("");

    let dis = $(this).val();
	
	if(upazila_list){
		var upazila_list_by_dis = upazila_list.filter(obj => obj.district_id == dis);
		options = "<option selected value=''>Thana</option>";
		upazila_list_by_dis.forEach(function(item) {
			options += `<option value="${item.id}">${item.name}</option>`;
		});
		$("#thana").html(options).niceSelect('update');
	}

	if(haor_list){
		var haor_list_by_dis = haor_list;
		if(dis){
			haor_list_by_dis = haor_list.filter(obj => obj.district_id == dis);
		}
		//console.log(dis)
		
		//console.log(haor_list_by_dis)
		options = "";
		haor_list_by_dis.forEach(function(item) {
			options += `<option data-id="${item.id}">${item.name}</option>`;
		});
		$("#haor-list").html(options);
	}
  });

  $(document).on("change", "#thana",function(e){
    e.preventDefault();
	$("#haor-list").empty();
	$("#haor").val("");

    let thana = $(this).val();
	
	if(haor_list){
		var haor_list_by_upa = haor_list.filter(obj => obj.upazila_id == thana);
		//console.log(haor_list_by_upa)
		options = "";
		haor_list_by_upa.forEach(function(item) {
			options += `<option data-id="${item.id}">${item.name}</option>`;
		});
		$("#haor-list").html(options);
	}
    console.log(thana);
  });

  $(document).on("click", "#haor_search_form",function(e){
    e.preventDefault();

    let haor = $("#haor").val();
	let thana = $("#thana").val();
	let district = $("#district").val();
	
	if(haor && haor_list){
		var haor_item = haor_list.find(obj => obj.name == haor);
		if(haor_item){
			location.href = "/haor-detail/"+haor_item.id;
		}
		else{
			alert("It's not available");
		}
	}
	else if(thana){
		location.href = "/upazila/"+thana;
	}
	else if(district){
		location.href = "/district/"+district;
	}
	else{
		alert("Please type a Haor name");
	}
    console.log(haor);
  });

  
  $('.closeBtn').click(function(){
	//$('#locationModal').modal('toggle');
	$('#locationModal').modal('hide');
	$('#locationModalInsideMap').modal('hide');
  });






