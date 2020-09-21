<?php
/**
 * @version 0.0.1
 */
/*
Plugin Name: get-remoto-post

Description: Teste exemplos remoto post
Author: Anselmo Paixao
Version: 0.0.1
*/
class KDM_PostRemoto{
	
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
		add_action('admin_menu', array('KDM_PostRemoto','admin_menu'));
	}
	function admin_menu(){
		add_menu_page('Dados remotos', 'Dados remotos', 'administrator', 'grp-data',array('KDM_PostRemoto','options_form'));
	}
	function options_form(){
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
			<form method="post" action="options.php">
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
	function settings(){
		$opt = get_option(self::$prefix,'opt');
		if(!$opt || !is_array($opt)){
			$opt = array(
					'search' => '',
					'cout'	=>''
			);
		}
		add_settings_section('grp-section', 'Opções personalizadas', array('KDM_PostRemoto','section'), 'grp');
		add_settings_field(
				'grp-search',
				'Termos de Busca',
				array('KDM_PostRemoto','text'),
				'grp',
				'grp-section',
				array(
						'name'=>'search',
						'value'=>$opt['seach']
				)
				);
		register_setting('grp', self::$prefix.'opt',array('KDM_PostRemoto','check_count'));
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
		KDM_PostRemoto::show_posts();
	}
}


register_activation_hook(__FILE__, array('KDM_PostRemoto','activation'));
register_deactivation_hook(__FILE__, array('KDM_PostRemoto','deactivation'));
add_action('plugin_loaded',array('KDM_PostRemoto','init'));
add_action('admin_init',array('KDM_PostRemoto','settings'));








