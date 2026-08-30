/**
 * 목차 — 지금 읽는 절을 강조하고, 눌렀을 때 그 자리로 옮겨 간다.
 *
 * 판정은 스크롤 위치로 한다. IntersectionObserver 는 "화면에 보이는가"를 다루는데,
 * 목차가 답해야 하는 것은 "어느 절을 읽고 있는가"라서 기준이 다르다.
 * 관찰 구간에 헤딩이 하나도 없는 구간(절 하나가 화면보다 긴 경우)에서
 * 갱신이 멈추는 문제가 실제로 있었다.
 */
( function () {
	'use strict';

	var toc = document.getElementById( 'gpc-toc' );

	if ( ! toc ) {
		return;
	}

	var links = [].slice.call( toc.querySelectorAll( 'a[data-target]' ) );

	if ( ! links.length ) {
		return;
	}

	var headings = links
		.map( function ( a ) {
			return document.getElementById( a.getAttribute( 'data-target' ) );
		} )
		.filter( Boolean );

	if ( ! headings.length ) {
		return;
	}

	// 상단 고정 바 높이 + 여유. 이 선을 지난 절을 읽고 있는 것으로 본다.
	var OFFSET = 100;

	// 스크롤해 올라갔을 때 목차가 멈추는 높이.
	var STICKY_TOP = 100;

	// 목차의 처음 자리는 작성자 줄 다음이다. 거기서 시작해 위로 올라가다가
	// STICKY_TOP 에 닿으면 멈춘다. position: sticky 로는 이 자리를 잡을 수 없다 —
	// 목차가 본문 흐름 밖(고정 배치)에 있어야 본문 폭을 건드리지 않기 때문이다.
	var anchor = document.querySelector( '.single .gpc-byline' )
		|| document.querySelector( '.single .entry-header' );

	var anchorBottom = 0;
	var positions = [];
	var activeId = null;
	var ticking = false;

	var positionToc = function () {
		toc.style.top = Math.max( STICKY_TOP, anchorBottom - window.scrollY ) + 'px';
	};

	var measure = function () {
		if ( anchor ) {
			anchorBottom = anchor.getBoundingClientRect().bottom + window.scrollY + 8;
		}

		positions = headings.map( function ( h ) {
			return {
				id: h.id,
				top: h.getBoundingClientRect().top + window.scrollY,
			};
		} );
	};

	var setActive = function ( id ) {
		if ( id === activeId ) {
			return;
		}

		activeId = id;

		links.forEach( function ( a ) {
			a.parentElement.classList.toggle(
				'is-active',
				a.getAttribute( 'data-target' ) === id
			);
		} );
	};

	var update = function () {
		var y = window.scrollY + OFFSET;
		var current = positions[ 0 ];

		for ( var i = 0; i < positions.length; i++ ) {
			if ( positions[ i ].top <= y ) {
				current = positions[ i ];
			} else {
				break;
			}
		}

		// 문서 끝에 닿으면 마지막 절을 활성으로 둔다.
		// 마지막 절이 짧으면 위 계산만으로는 도달하지 못한다.
		if ( window.innerHeight + window.scrollY >= document.body.scrollHeight - 2 ) {
			current = positions[ positions.length - 1 ];
		}

		if ( current ) {
			setActive( current.id );
		}
	};

	var onScroll = function () {
		if ( ticking ) {
			return;
		}

		ticking = true;

		window.requestAnimationFrame( function () {
			positionToc();
			update();
			ticking = false;
		} );
	};

	measure();
	positionToc();
	update();
	toc.classList.add( 'is-ready' );

	window.addEventListener( 'scroll', onScroll, { passive: true } );
	window.addEventListener( 'resize', function () {
		measure();
		positionToc();
		update();
	} );

	var remeasure = function () {
		measure();
		positionToc();
		update();
	};

	// 웹폰트와 이미지가 늦게 뜨면 헤딩과 작성자 줄 위치가 밀린다. 다 뜬 뒤 다시 잰다.
	// load 가 이미 지난 뒤에 이 스크립트가 실행되는 경우가 있어 상태를 직접 본다.
	if ( 'complete' === document.readyState ) {
		remeasure();
	} else {
		window.addEventListener( 'load', remeasure );
	}

	if ( document.fonts && document.fonts.ready ) {
		document.fonts.ready.then( remeasure );
	}

	links.forEach( function ( a ) {
		a.addEventListener( 'click', function ( e ) {
			var target = document.getElementById( a.getAttribute( 'data-target' ) );

			if ( ! target ) {
				return;
			}

			e.preventDefault();

			window.scrollTo( {
				top: target.getBoundingClientRect().top + window.scrollY - 80,
				behavior: window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches
					? 'auto'
					: 'smooth',
			} );

			history.replaceState( null, '', '#' + target.id );
			setActive( target.id );
		} );
	} );
}() );
