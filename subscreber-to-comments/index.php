<?php
/**
 * @package ççlkçlk
 * @version 0.0.1
 */
/*
Plugin Name: subscreber 2 comments

Description: Teste exemplos
Author: Anselmo Paixao
Version: 0.0.1
*/
/* add in config para tisparo de email sem outros plugin
 * define('MAIL_HOST'				, "smtp.gmail.com");
 * define('MAIL_PORT'				, "465");
 * define('MAIL_USER'				, "e-mail@gmail.com");
 * define('MAIL_PASSWORD'			, "senha");
 * define('MAIL_SMTPSECURE'		, "ssl");
 * define('MAIL_REMETENTE'			, "");
 * define('MAIL_DESTINATARIO'		, "");
 */
require_once 'semvMail.class.php';

class KDM_Subscriber{
	static $field_name = 'kdms_subscriber';
	
	function init() {
		add_action('comment_form', array('KDM_Subscriber','show_form'));
		add_action('comment_post', array('KDM_Subscriber','check_subscriber'),10,2);
		add_filter('wp_mail_content_type', create_function('','return "text/html";'));
		add_action('wp_loaded', array('KDM_Subscriber','subscriber'));
	}
	function show_form(){
		
		
		?>
			<label>
				<input type="checkbox" name="<?php echo self::$field_name;?>" value="1" />
				Desejo receber notificações de novos comentarios.
			</label>
		<?php 	
	}
	function check_subscriber($comment_id, $approved) {
		
		if(isset($_POST[self::$field_name]) && $_POST[self::$field_name]){
			$comment = array(
					'comment_ID' => $comment_id,
					'comment_karma'=> '1'
					);
			wp_update_comment($comment);
		}
		if($approved){
			$comment = get_comment($comment_id);
			self::notify($comment);
		}
	}
	function notify($comment) {
		
		$post_id = $comment->comment_post_ID;
		$permalink = get_permalink($post_id);
		$messagem = sprintf(
				'novo comentario para %.<a href="%s">clique aqui</a> para visualizar.<br>'.
					'<a href="[unsubscribe]">cancelar notificação</a>',
					get_the_title($post_id),
					$permalink
				);
		
		$comments = get_comments(array(
					'post_id'=> $post_id,
					'karma' => '1'
					));
					
		foreach ($comments as $value) {
			
			if($value->comment_ID != $comment->comment_id){
				$url = add_query_arg('unsubscribe',$value->comment_ID);
				$messagem = str_replace('[unsubscribe]',$url,$messagem);
// 				wp_mail($comment->comment_author_email, 'notofocação de comentarios', $messagem);
				try {
					ob_start ();
					
					// 			$avisoEmail = semvMail::sendMail ( $assunto, $corpoEmail, array($emailCad), null, null );
					$avisoEmail = semvMail::sendMail ( 'notifocação de comentarios', $messagem, array($comment->comment_author_email), null, null );
					
					ob_clean ();
					if($avisoEmail){
						var_dump( 'Erro no disparo do email!');die;
					}else{
						
					}
				} catch ( Exception $e ) {
					$msg = 'Erro ';
					var_dump( $msg);die;
					
				}
			}
			
		}
	}
	function subscriber() {
		
		$comment_id = isset($_GET['unsubscribe'])?(int)$_GET['unsubscribe']:false;
// 		var_dump(is_single() ,$comment_id);die;
		if( $comment_id){
			
			$c = get_comment($comment_id);
			if($c->comment_karma == '1'){
				$comment = array(
							'comment_ID'=> $comment_id,
							'comment_karma' => '0'
				);
				
				wp_update_comment($comment);
				
				$msg =' Cancelamento Confirmado!';
			}else{
				$msg =' Já foi cancelado!';
			}
			wp_die($msg);
		}
	}
}
add_action('plugin_loaded', array('KDM_Subscriber','init'));
