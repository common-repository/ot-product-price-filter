<div class="form-group">
    <p><?php echo otpf_e('Widget title'); ?></p>
    <input type="text" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo $title; ?>" class="form-control widefat" />
</div>
<div class="form-group">
    <p><?php echo otpf_e('Slider min price'); ?></p>
    <input type="number" id="<?php echo $this->get_field_id('minValue'); ?>" name="<?php echo $this->get_field_name('minValue'); ?>" value="<?php echo $minValue; ?>" class="form-control widefat" />
</div>
<div class="form-group">
    <p><?php echo otpf_e('Slider max price'); ?></p>
    <input type="number" id="<?php echo $this->get_field_id('maxValue'); ?>" name="<?php echo $this->get_field_name('maxValue'); ?>" value="<?php echo $maxValue; ?>" class="form-control widefat" />
</div>
<div class="form-group">
    <p><?php echo otpf_e('Slider start min price'); ?></p>
    <input type="number" id="<?php echo $this->get_field_id('minValueStart'); ?>" name="<?php echo $this->get_field_name('minValueStart'); ?>" value="<?php echo $minValueStart; ?>" class="form-control widefat" />
</div>
<div class="form-group">
    <p><?php echo otpf_e('Slider start max price'); ?></p>
    <input type="number" id="<?php echo $this->get_field_id('maxValueStart'); ?>" name="<?php echo $this->get_field_name('maxValueStart'); ?>" value="<?php echo $maxValueStart; ?>" class="form-control widefat" />
</div>
<div class="form-group">
    <p><?php echo otpf_e('Widget class'); ?></p>
    <input type="text" id="<?php echo $this->get_field_id('widgetclass'); ?>" name="<?php echo $this->get_field_name('widgetclass'); ?>" value="<?php echo $widgetclass; ?>" class="form-control widefat" />
</div>