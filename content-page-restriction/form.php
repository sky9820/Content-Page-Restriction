<div class="hcf_box">
    <style scoped>
        .hcf_box{
            display: grid;
            grid-template-columns: max-content 1fr;
            grid-row-gap: 10px;
            grid-column-gap: 20px;
        }
        .hcf_field{
            display: contents;
        }
    </style>
	
	<?php
		$editable_roles = get_editable_roles();
		foreach ($editable_roles as $role => $details) {
			$sub['role'] = esc_attr($role);
			$sub['name'] = translate_user_role($details['name']);
			$roles[] = $sub;
		}
	?>
	
    <p class="meta-options hcf_field">
        <p>Choose below User Roles who will access this page.</p><br>
        <div class="checkbox-list">
			<ul>
				<?php 
				$value = get_post_meta( get_the_ID(), 'hcf_user_role', true ); 
				$savedVal = json_decode($value);
				foreach($roles as $role){ ?>
					<li>
					<?php 
						$sel = '';
						if(!empty($savedVal)){
							if(in_array( $role['role'], $savedVal) ){ 
								$sel = 'checked="checked"'; 
							} 
						}
					?>
					<input type="checkbox" name="hcf_user_role[]" <?php echo esc_attr($sel); ?> value="<?php echo esc_attr($role['role']);?>"> <?php echo esc_html($role['name']); ?>
					</li>
				<?php } ?>
				<?php
					$sel = '';
					if(!empty($savedVal)){
						if(in_array('guest', $savedVal) ){ 
							$sel = 'checked="checked"'; 
						} 
					}
				?>
				<li><input type="checkbox" name="hcf_user_role[]" <?php echo esc_attr($sel); ?> value="guest"> Guest User</li>
			</ul>
		</div>
    </p>
</div>