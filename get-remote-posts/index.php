<?php 
/**
 * @package teste1Netto234
 * @version 0.0.1
 */
/*
/*
  Plugin Name: recuperar post remoto
  Description: Recuperação informação remotas
  Version: 1.0
  Author: Netto
 * 
 */

class KDM_Posts{
	
	static  $prefix = 'grp';
	
	function activation(){
		
		$opt = array(
				'search' => 'wordpress',
				'count'	=> 3
				);
		add_option(self::$prefix,'opt',$opt,false,'no');
	}
	function deactivation(){
		delete_option(self::$prefix,'opt');
		delete_transient('grp');
	}
	function init(){
		add_action('admin_menu', array('KDM_Posts','admin_menu'));
	}
	function admin_menu(){
		add_menu_page('Dados remotos', 'Datos remotos', 'adminstrador', 'grp-data',array('KDM_Posts','options_form'));
	}
	function option_form(){
		$tab_cur = isset($_GET['tab'])? $_GET['tab'] : 'opt';
		
		if(isset($_GET['settings-update'])){
			delete_transient('grp');
		}
		?>
		<div class='warp get-remote-post'>
			<h2>Configurações do Plugin</h2>
			<h2 class='nav-tab-warpper'>
				<?php 
					$tabs = array(
								'opt'=>'Opções',
								'help'=>'Suporte Teste'
								);
					foreach ($tabs as $key=>$valores){
						printf(	
							'<a href="%s" class="nav-tab%s">%s</a>',
							admin_url('admin.php?page=grp-data&tab='.$tabs),
							($tab == $tab_cur)?'nav-tab-active':'',
							$lavel
						);
					}
				?>
			</h2>
			<?php if($tab_cur == 'opt'){?>
			<?php settings_errors();?>
			<form method="post" actiona="options.php">
				<?php settings_fields('grp');?>
				<?php do_settings_sections('grp');?>
				<?php submit_button()?>
			</form>
			<?php } else {
				echo '<p> tela de Suporte do plugin... </p>';
			}?>
		</div>
		<?php
	}
	
	function setting(){
		$opt = get_option(self::$prefix,'opt');
		if(!$opt || !is_array($opt)){
			$opt = array(
					'search' => '',
					'cout'	=>''
			);
		}
		add_settings_section('grp-section', 'Opções personalizadas', array('KDM_Posts','section'), 'grp');
		add_settings_field(
				'grp-search', 
				'Termos de Busca', 
				array('KDM_Posts','text'), 
				'grp',
				'grp-section',
				array(
						'name'=>'search',
						'value'=>$opt['seach']
				)
		);
		register_setting('grp', self::$prefix.'opt',array('KDM_Posts','check_count'));
	}
	function section(){
		echo 'Abrir seção';
	}
	function text(){
		echo '<input type="tesxt" name="'.self::$prefix.'opt['.$arg['name'].']" value="'.$arg['value'].'"/>';
	}
	function check_count($value){
		$num = (int)$value['count'];
		if(!$num){
			$value =false;
			add_settings_error('count', 'isNan', 'O valor informato nnão é numero!');
		}
		return $value;
	}
	function get_posts(){
		$posts = array();
		$opt = get_option(self::$prefix.'opt');
		if(!is_archive($opt)){
			return false;
		}
		$url = sprintf(
				'http://localhost/remote-posts.php/s=%s&count=%d',
				$opt['search'],
				(int)$opt['count']	
		);
		$r = wp_remote_get($url,array('sslverify' => false));
		$data = json_decode($r['body']);
		if(is_object($data) && isset($data->status)){
			if($data->status == 'success'){
				foreach($data->content as $d){
					$post = sprintf(
							'<a href="$l$s" title="%2$s"</a> em %3$s',
							$d->url,
							$d->title,
							data('d/m/Y',strtotime($d->date))
					);
					array_push($posts,$post);
				}
			}else{
				array_push($posts,$data->content);
			}
		}else{
			array_push($posts,'Não foi possivel acessar o servidor remoto.');
		}
		return $posts;
	}
	function show_posts(){
		$posts = self::get_posts();
		echo '<div class="get-remote-posts">'.
				'<h2>Publicações remotas</h2>'.
				'<url>';
		foreach ($posts as $p){
			echo '<li>{$p}</li>';
		}
		echo '<ul></div>';
	}
	
	function list_remote_post(){
		KDM_Posts::show_posts();
	}
}

register_activation_hook(__FILE__, array('KDM_Posts','activation'));
register_deactivation_hook(__FILE__, array('KDM_Posts','deactivation'));
add_action('plugins_loaded',array('KDM_Posts','init'));
add_action('admina_init',array('KDM_Posts','settings'));




?>














