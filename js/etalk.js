var currentSnd = -1;
var player = document.getElementById("player");
var kFadeDuration = 200;
function play() {
	document.getElementById("bPlay").style.display="none";
	document.getElementById("bPause").style.display="inline";
	player.play();
	return false;
}
function pause() {
	document.getElementById("bPlay").style.display="inline";
	document.getElementById("bPause").style.display="none";
	player.pause();
	return false;
}
function startedPlay() {
	$("html, body").animate({scrollTop: $("#a"+currentSnd).offset().top-50}, 1000);
}
function endedPlay() {
	next();
}
function setCurrentSnd(c) {
	currentSnd = c;
	location.hash = c;
	player.src = 'data/'+audioFiles[currentSnd]['snd'];
	$('#viz a').removeClass('current');
	$('#a'+currentSnd).addClass('current');
	if ('/tmp/'+audioFiles[currentSnd]['pict']!=$('#diaPict').attr('src')) {
		$('#diaPict').fadeOut(kFadeDuration, function(){
			if (audioFiles[currentSnd]['pict']!=='') {
				$('#diaPict').attr('src', '/tmp/'+audioFiles[currentSnd]['pict']);
			}
		});
	}
	$('#links').fadeOut(kFadeDuration, function(){
		if (audioFiles[currentSnd]['link']!=='') {
			$('#links').html(audioFiles[currentSnd]['link']);
			$('#links').fadeIn();
		}
	});
}
function playTrack(index) {
	player.pause();
	setCurrentSnd(index);
	play();
	return false;
}
function next() {
    if (currentSnd < audioFiles.length-1) {
		setCurrentSnd(currentSnd+1);
		play();
	}
	else {
		alert('End of the track.');
	}
	return false;
}
function openLink(url) {
	$('#overlay iframe').attr('src', url);
	$('#overlay').fadeIn();
	return false;
}
function hideOverlay() {
	$('#overlay').fadeOut();
	$('#overlay iframe').attr('src', '');
	return false;
}
function start() {
	setCurrentSnd(0);
	$('#wait').fadeOut();
	playTrack(currentSnd);
	return false;
}
function toggleMode() {
	if ($('#viz').offset().left<0) {
		$('#btn_mode').attr('src', 'i/mode_full.png');
		$('#viz').animate({'margin-left':0});
		$('#dia').animate({'left':($('#viz').width()+60)});
	}
	else {
		$('#btn_mode').attr('src', 'i/mode_list.png');
		$('#viz').animate({'margin-left':-($('#viz').width()+60)});
		$('#dia').animate({'left':0});
	}
	return false;
}
if (window.location.hash) { $("#wait").hide(); playTrack(parseInt(window.location.hash.substring(1), 10)); }

$(document).ready(function(){
	$("#diaPict").load(function(){ $(this).fadeIn(); });
	$('#bMode').click(function(e){ e.preventDefault(); toggleMode(); });
	$('#bPlay').click(function(e){ e.preventDefault(); play(); });
	$('#bPause').click(function(e){ e.preventDefault(); pause(); });
	$('#bNext').click(function(e){ e.preventDefault(); next(); });
	$('#links a.entity').click(function(e){ e.preventDefault(); e.stopPropagation(); openLink($(this).attr('href')); });
	$('#wait a').click(function(e){ e.preventDefault(); start();});
	$('#overlay .close').click(function(e){ e.preventDefault(); hideOverlay(); });
});