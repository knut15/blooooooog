/**
 * 글 제목이 화면 위로 사라지면 상단에 sticky 바를 띄운다.
 */
( function () {
	'use strict';

	var bar = document.getElementById( 'gpc-sticky-bar' );
	var header = document.querySelector( '.single .entry-header' );

	if ( ! bar || ! header ) {
		return;
	}

	var show = function ( visible ) {
		bar.classList.toggle( 'is-visible', visible );
		bar.setAttribute( 'aria-hidden', visible ? 'false' : 'true' );
	};

	if ( 'IntersectionObserver' in window ) {
		// 제목 영역이 화면에서 완전히 벗어나면 바를 띄운다.
		var observer = new IntersectionObserver(
			function ( entries ) {
				show( ! entries[ 0 ].isIntersecting );
			},
			{ rootMargin: '-8px 0px 0px 0px', threshold: 0 }
		);
		observer.observe( header );
	} else {
		// IntersectionObserver 미지원 브라우저 폴백
		var onScroll = function () {
			show( window.scrollY > header.offsetTop + header.offsetHeight );
		};
		window.addEventListener( 'scroll', onScroll, { passive: true } );
		onScroll();
	}
}() );
