<div class="wrap">
    <?php if( !empty($tzMessage) ): ?>
        <div class="notice notice-<?php echo $tzMessage['type']; ?> is-dismissible" id="message">
            <p><?php echo $tzMessage['msg']; ?></p>
            <button class="notice-dismiss" type="button"></button>
        </div>
    <?php endif; ?>
    <div id="poststuff">
        <div class="postbox-container">
            <div class="meta-box-sortables ui-sortable" id="normal-sortables">
                <div class="postbox " id="profile_fields">
                    <h3 class="hndle ui-sortable-handle"><span><?php _e( 'Connexion MDWorks', 'mdworks-hosted' ); ?></span></h3>
                    <div class="inside">
					<?php if( $zLogin == false ): ?>
                        <form name="frm-login" method="post" action="">
                            <table class="form-table">

                                <tbody><tr>
                                    <td colspan="2" class="login_meta_box_td">
                                        <label for="login"><?php _e( 'Nom d\'utilisateur', 'mdworks-hosted' ); ?></label>
                                    </td>
                                    <td colspan="4">
                                        <input type="text" value="<?php echo get_option( 'mdworks_hosted_login' ); ?>" placeholder="<?php _e( 'Login', 'mdworks-hosted' ); ?>" class="regular-text" name="login" required>
                                    </td>
                                </tr>

                                <tr>
                                    <td colspan="2" class="password_meta_box_td">
                                        <label for="password"><?php _e( 'Mot de passe', 'mdworks-hosted' ); ?></label><br>
                                    </td>
                                    <td colspan="4">
                                        <input type="password" value="<?php echo get_option( 'mdworks_hosted_password' ); ?>" class="regular-text" name="password" placeholder="<?php _e( 'Mot de passe', 'mdworks-hosted' ) ?>" required>
                                    </td>
                                </tr>

                                <tr>
                                    <td colspan="2">&nbsp;</td>
                                    <td colspan="4">
                                        <input type="submit" value="<?php _e( 'Connexion', 'mdworks-hosted' ); ?>" class="button button-primary button-large" name="save">
                                        <?php wp_nonce_field("mdworks_login", "mdworks_wpnonce"); ?>
                                    </td>
                                </tr>

                                </tbody>
                            </table>
                        </form>
						<?php else: ?>
						<div class="notice notice-success is-dismissible" id="message">
							<p>Le plugin MDWorks est bien connecté au compte : <?php echo get_option( 'mdworks_hosted_login' ); ?></p>
							<p><a type="button" href="<?php echo add_query_arg('logout', '1', admin_url('options-general.php?page=hostedlists')); ?>" class="button button-primary button-large"><?php _e( 'Déconnexion', 'mdworks-hosted' ); ?></a></p>
							<!--button class="notice-dismiss" type="button"></button-->
						</div>
						<?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="postbox-container">
            <div class="meta-box-sortables ui-sortable" id="normal-sortables">
                <div class="postbox " id="profile_fields">
                    <h3 class="hndle ui-sortable-handle"><span><?php _e( 'Sélection d\'une base de données hébergée sur MDWorks', 'mdworks-hosted' ); ?></span></h3>
                    <div class="inside">
                        <form name="frm-db" id="frm-db" method="post" action="">
                            <table class="form-table">

                                <tbody>
                                    <tr>
                                        <td colspan="2" class="login_meta_box_td">
                                            <label for="login"><?php _e( 'Base de données hébergée', 'mdworks-hosted' ); ?> :</label>
                                        </td>
                                        <td colspan="4" style="width:82%">
                                            <?php if( isset($iDbSelect) && is_numeric($iDbSelect) ): ?>
                                                <?php if( $oLstDataBase ): ?>
                                                    <?php foreach( $oLstDataBase as $db): ?>
                                                        <?php if($iDbSelect == $db['id']): ?>
                                            <label>
                                                        <?php echo utf8_encode($db['reference']); break; ?>
                                            </label>
                                                        <?php endif; ?>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            <?php else: ?>
                                            <select style="width: 262px;" name="slt_db" id="slt_db" required>
                                                <option value=""><?php _e( 'Choisissez une base sur MDWorks', 'mdworks-hosted' ); ?></option>
                                                <?php if( $oLstDataBase ): ?>
                                                    <?php foreach( $oLstDataBase as $db): ?>
                                                        <option value="<?php echo $db['id']; ?>"><?php echo utf8_encode($db['reference']); ?></option>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </select>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php if( isset($iDbSelect) && is_numeric($iDbSelect) ): ?>
                                    <tr>
                                        <td colspan="6" style="padding-top: 0;">
                                            <a type="button" href="<?php echo add_query_arg('delmapping', '1', admin_url('options-general.php?page=hostedlists')); ?>" class="button button-primary button-large"><?php _e( 'Supprimer la liaison', 'mdworks-hosted' ); ?></a>
                                        </td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                            <?php wp_nonce_field("mdworks_db", "mdworks_wpnonce"); ?>                            
                        </form>                        
                    </div>
                </div>
            </div>
        </div>
<?php /*
        <div class="postbox-container">
            <div class="meta-box-sortables ui-sortable" id="normal-sortables">
                <div class="postbox " id="profile_fields">
                    <h3 class="hndle ui-sortable-handle"><span><?php _e( 'FORM DATABASE', 'mdworks-hosted' ); ?></span></h3>
                    <div class="inside">
                        <form name="frm-insert" id="frm-db" method="post" action="">
                            <?php if( isset($tzStmt) && is_array($tzStmt)): ?>
                                <input type="hidden" name="slt_db" value="<?php echo $iDbSelect; ?>">
                                <input type="hidden" value="<?php echo time(); ?>" name="fields[]">
                                <input type="hidden" value="<?php echo time(); ?>" name="fields[]">
                                <table class="form-table">
                                    <tbody>
                                    <?php foreach($tzStmt as $k => $stmt): ?>
                                        <?php if( $k > 1 ): ?>
                                            <tr>
                                                <td colspan="2" class="login_meta_box_td">
                                                    <label for="field_<?php echo $k ?>"><?php echo ucfirst($stmt['field']); ?></label>
                                                </td>
                                                <td colspan="4">
                                                    <input type="text" value="" placeholder="<?php echo $stmt['field']; ?>" class="regular-text" id="field_<?php echo $k ?>" name="fields[]" required>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                    <tr>
                                        <td colspan="2">&nbsp;</td>
                                        <td colspan="4">
                                            <input type="submit" value="<?php _e( 'Inserer', 'mdworks-hosted' ); ?>" class="button button-primary button-large" name="<?php _e( 'Inserer', 'mdworks-hosted' ); ?>">
                                            <?php wp_nonce_field("mdworks_insert", "mdworks_wpnonce"); ?>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <div class="notice notice-error is-dismissible" id="message">
                                    <p><?php _e( 'Aucune base de données sélectionnée.', 'mdworks-hosted' ); ?></p>
                                    <button class="notice-dismiss" type="button"></button>
                                </div>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
            </div>
        </div>
*/ ?>
        <div class="postbox-container">
            <div class="meta-box-sortables ui-sortable" id="normal-sortables">
                <div class="postbox " id="profile_fields">
                    <h3 class="hndle ui-sortable-handle"><span><?php _e( 'Correspondance entre les champs', 'mdworks-hosted' ); ?></span></h3>
                    <div class="inside">
                        <form name="frm-mapping" id="frm-mapping" method="post" action="">
                            <?php if( isset($tzStmt) && is_array($tzStmt)): ?>
                                <?php if( count($tzStmt) > 2 ): ?>
                                <input type="hidden" name="slt_db" value="<?php echo $iDbSelect; ?>">
                                <table class="form-table">
                                    <thead>
                                        <tr>
                                            <td colspan="1"><h3 style="padding-left: 0px; padding-right: 0px; padding-bottom: 0px;"><?php _e('Base utilisateurs Wordpress', 'mdworks-hosted'); ?></h3></td>
                                            <td colspan="1"></td>
                                            <td colspan="1"><h3 style="padding-left: 0px; padding-right: 0px; padding-bottom: 0px;"><?php _e('Base de données hébergée sur MDWorks', 'mdworks-hosted'); ?></h3></td>
                                        </tr>
                                    </thead>
                                    <tbody>                                        
                                    <?php foreach($tzStmt as $k => $stmt): ?>
                                        <?php if( $stmt['field'] != 'system_date_modif' && $stmt['field'] != 'system_date_entree' ): ?>
                                            <tr>
                                                <td colspan="1">
                                                    <select style="width: 262px;" name="user_stmt[]" required>
                                                        <option value=""><?php _e( 'Sélectionnez un champ', 'mdworks-hosted' ); ?></option>
                                                        <option <?php if(isset($tzMapping[$k]) && $tzMapping[$k] == 'user_login') echo 'selected'; ?> value="user_login"><?php _e( 'Identifiant', 'mdworks-hosted' ); ?></option>
                                                        <option <?php if(isset($tzMapping[$k]) && $tzMapping[$k] == 'user_email') echo 'selected'; ?> value="user_email"><?php _e( 'E-mail', 'mdworks-hosted' ); ?></option>
                                                        <option <?php if(isset($tzMapping[$k]) && $tzMapping[$k] == 'first_name') echo 'selected'; ?> value="first_name"><?php _e( 'Prénom', 'mdworks-hosted' ); ?></option>
                                                        <option <?php if(isset($tzMapping[$k]) && $tzMapping[$k] == 'last_name') echo 'selected'; ?> value="last_name"><?php _e( 'Nom', 'mdworks-hosted' ); ?></option>
                                                        <option <?php if(isset($tzMapping[$k]) && $tzMapping[$k] == 'user_url') echo 'selected'; ?> value="user_url"><?php _e( 'Site web', 'mdworks-hosted' ); ?></option>                                                        
                                                    </select>
                                                    <input type="hidden" value="<?php echo $k ?>" name="db_stmt[]">
                                                </td>
                                                <td colspan="1">=></td>
                                                <td colspan="1" class="login_meta_box_td">
                                                    <label for="field_<?php echo $k ?>"><?php echo ucfirst($stmt['field']); ?></label>
                                                </td>                                                
                                            </tr>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                    <tr>
                                        <td colspan="2">
                                            <input type="submit" value="<?php _e( 'Enregistrer et ajouter les utilisateurs existants', 'mdworks-hosted' ); ?>" class="button button-primary button-large" name="mapping">
                                            <?php wp_nonce_field("mdworks_mapping", "mdworks_wpnonce"); ?>
                                        </td>
                                        <td colspan="1">&nbsp;</td>                                        
                                    </tr>
                                    </tbody>
                                </table>
                                <?php else: ?>
                                <div class="notice notice-error is-dismissible" id="message">
                                    <p><?php _e( 'Ce base de données ne contient pas de champs.', 'mdworks-hosted' ); ?></p>
                                    <button class="notice-dismiss" type="button"></button>
                                </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="notice notice-error is-dismissible" id="message">
                                    <p><?php _e( 'Aucune base de données sélectionnée.', 'mdworks-hosted' ); ?></p>
                                    <button class="notice-dismiss" type="button"></button>
                                </div>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
