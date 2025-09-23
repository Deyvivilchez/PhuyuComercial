
<div class="menu-container flex-grow-1" id="sidebar-menu">
    <ul id="menu" class="menu">
    	<?php $i = 0;
            foreach ($phuyu_modulos as $key => $value) { $i++;  
                if(count($value["submodulos"])>0){ ?>
                    <li>
                    	<a href="#dashboards<?php echo $i;?>">
		                  <i data-acorn-icon="<?php echo $value["icono"];?>" class="icon" data-acorn-size="18"></i>
		                  <span class="label" style="text-transform: capitalize"><b><?php echo $value["descripcion"];?></b></span>
		                </a>
		                <ul id="dashboards<?php echo $i;?>">
                            <?php 
                                foreach ($value["submodulos"] as $val) { ?>
                                	<li>
                                		<a href="<?php echo base_url().'phuyu/w/'.$val["url"];?>"> <i class="fa fa-circle-thin" style="font-size: 11px" aria-hidden="true"></i> <?php echo $val["descripcion"];?></a>
                                	</li>
                                <?php }
                            ?>
                        </ul>
                    </li>
                <?php } ?>
            <?php }
        ?>
    </ul>
</div>

