<?php
	header('Vary: Accept');
	header('Content-Type: text/html; charset=utf-8');
	date_default_timezone_set('Europe/Zurich');
	include('_db.php');
	include('_formutils.php');

	$viewMode = (@$_REQUEST['dir']!='' && substr($_REQUEST['dir'], 0, 1)!='.');
	if ($viewMode) {
    	$talk = db_fetch(db_s('talks', array('dir' => $_REQUEST['dir'])));
    	define('PAGE_TITLE', 'eTalk | '.$talk['title']);
	}
	else {
		define('PAGE_TITLE', 'eTalk');
	}

	echo '<!DOCTYPE HTML><html><head><title>'.PAGE_TITLE.'</title>';
		echo '<link rel="stylesheet" type="text/css" media="screen" href="/s/screen.css" />';
		echo '<script type="text/javascript" src="/js/jquery.min.js"></script>';
		echo '<script type="text/javascript" src="/js/jquery.color.min.js"></script>';
		echo '<script type="text/javascript" src="/js/jquery.animate-shadow-min.js"></script>';
	echo '</head><body class="viewer'.($viewMode?' paused':'').'">';


    // Page Content ============================================================================================================================================
    if ($viewMode) {
	    echo '<header>';
	    	echo '<nav><img src="/i/back.png" id="bBack" alt="Back" class="btn" title="Retour à l’accueil" /></nav>';
#	    	echo '<h1>'.$talk['title'].'</h1>';
#	    	echo '<h2>'.$talk['author'].' &mdash; '.implode('.', array_reverse(explode('-', $talk['date']))).'</h2>';
	    	echo '<nav id="controls">';
	    		echo '<img src="/i/loading.gif" id="loading" class="btn" alt="" /> ';
				echo '<img src="/i/share.png" id="bShare" class="btn" alt="SHARE" title="Partager / Intégrer" />';
				echo '<img src="/i/audio_mute.png" id="bMute" class="btn" alt="MUTE" title="Activer/Couper le son" />';
				echo '<img src="/i/mode_full.png" id="bMode" class="btn" alt="Afficher/Masquer le transcript" />';
				echo '<img src="/i/play.png" id="bPlay" class="btn" alt="PLAY" />';
				echo '<img src="/i/pause.png" id="bPause" class="btn" alt="PAUSE" />';
				echo '<img src="/i/ffw.png" id="bNext" class="btn" alt="FFW" title="Suivant" />';
			echo '</nav>';
	    echo '</header>';
	    // _______________________________________________________________________________________________________________________________________
    	echo '<div id="wait">';
    		echo '<header>';
	    		echo '<h1>'.$talk['title'].'</h1>';
	    		echo '<h2>'.$talk['author'].' &mdash; '.implode('.', array_reverse(explode('-', $talk['date']))).'</h2>';
	    		echo '<a href="#0" class="vidPlay">▶</a>';
	    	echo '</header>';
    		echo '<nav>';
	    		echo '<h2>Sommaire</h2>';
				$i=0;
				$chap_r = db_s('sounds', array('dir' => $_REQUEST['dir']), array('id' => 'ASC'));
				while ($chap = db_fetch($chap_r)) {
					if ($chap['chaptering']=='section') {
						echo '<a href="#'.$i.'">'.$chap['section_title'].'</a>';
					}
					$i++;
				}
				$docsFolder = 'data/'.$_REQUEST['dir'].'/docs';
				if (is_dir($docsFolder)) {
					echo '<br/><br/><h2>Fichiers liés</h2>';
					$files = scandir($docsFolder, 0);
					foreach ($files as $f) {
						if (substr($f, 0, 1)!='.' && !is_dir($docsFolder.'/'.$f)) {
							echo '<a href="/'.$docsFolder.'/'.$f.'" class="doc">'.$f.'</a>';
						}
					}
				}
    		echo '</nav>';
    	echo '</div>';
	    // _______________________________________________________________________________________________________________________________________
		echo '<aside id="embed"><div><div class="close">&times;</div><h1>Intégrer cette présentation</h1>';
			echo '<textarea id="embed_code" readonly="readonly"></textarea>';
			echo '<form id="embed_customize" action="/">';
				echo '<fieldset><legend>Dimensions :</legend>';
					echo '<div><input id="embed_w" type="text" value="720" /> &times; <input id="embed_h" type="text" value="405" /> pixels</div>';
				echo '</fieldset>';
				echo '<fieldset><legend>Options:</legend>';
				echo '<ul>
						<li>
							<input id="embed_desc" type="checkbox" checked="checked" /><label for="embed_desc"> Description sous la vidéo</label>
						</li>
						<li>
							<input id="embed_link" type="checkbox" checked="checked" /><label for="embed_link"> Lien permanent dans la description</label>
						</li>
					</ul>';
				echo '</fieldset>';
			echo '</form>';
		echo '</div></aside>';
	    // _______________________________________________________________________________________________________________________________________
    	echo '<div id="overlay">';
    		echo '<img src="/i/close.png" class="close" alt="&times;" title="Close" width="22" height="22" />';
    		echo '<iframe></iframe>';
    	echo '</div>';
	   	echo '<div id="viz">';
    	$audioFiles = array();
		$i=0;
		$sounds_r = db_s('sounds', array('dir' => $_REQUEST['dir']), array('id' => 'ASC'));
		while ($sound = mysql_fetch_assoc($sounds_r)) {
			$track = array(
							'snd' => $sound['id'],
							'pict' => $sound['file'],
							'pict_link' => $sound['file_link'],
							'pict_cred' => $sound['file_credits'],
							);
			$links = '';
			$e = preg_split('/\s/',$sound['entities']);
			foreach ($e as $entity) {
				if ($entity!='') {
					$links.= '<a href="'.$entity.'" class="entity"><img src="/i/link.png" alt="" />'.@array_shift(explode('/', str_replace('http://', '', $entity))).'</a>';
				}
			}
			$track['link'] = $links;
			if ($sound['chaptering']=='section') {
				echo '<h2>'.$sound['section_title'].'</h2>';
			}

			echo '<a class="'.$sound['type'].'" href="#'.$i.'" id="a'.$i.'">'.stripslashes($sound['text']).'</a>';
			$audioFiles[] = $track;
			$i++;
		}
		echo '</div>';
	    // _______________________________________________________________________________________________________________________________________
		echo '<div id="dia"><figure>';
			echo '<img id="diaPict" src="" alt="" />';
			echo '<caption></caption>';
		echo '</figure><div id="links"></div></div>';
		// _____________________________________
		echo '<audio id="player" preload="preload" src="/data/'.$audioFiles[0]['snd'].'" onerror="alert(\'The sound file \\\'\'+this.src+\'\\\' could not be loaded.\');" onended="endedPlay();" onloadstart="document.getElementById(\'loading\').style.display=\'inline\';" oncanplay="document.getElementById(\'loading\').style.display=\'none\';" onplay="startedPlay();"><source src="/data/'.$audioFiles[0]['snd'].'" type="audio/mp3" />HTML5 Only!</audio>';
#		echo '<audio id="preloader" preload="preload" src="/data/'.$audioFiles[1]['snd'].'"><source src="/data/'.$audioFiles[1]['snd'].'" type="audio/mp3" />HTML5 Only!</audio>';

	    // Load and init etalk modules
	    printJS('var audioFiles = ('.json_encode($audioFiles).');');
		echo '<script type="text/javascript" src="/js/etalk.min.js"></script>';
    }
    else {
    	echo '<header>';
    		echo '<h1>eTalk</h1><h2>Open-source online talks</h2>';
		echo '</header>';

		echo '<section><nav>';
			$talks = array('' => '(sélectionnez une conférence)');
			$r_t = db_s('talks', array(), array('title' => 'ASC'));
			while ($t = db_fetch($r_t)) {
				echo '<a href="?dir='.$t['dir'].'"><figure><div class="play"></div></figure><h2>'.$t['title'].'</h2><p>'.$t['author'].' ('.datetime('d.m.Y', $t['date']).')</p></a>';
			}
		echo '</nav></section>';
    }

    echo '</body></html>';

?>