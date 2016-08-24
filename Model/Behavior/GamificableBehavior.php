<?php

class GamificableBehavior extends ModelBehavior {

	public function setup(Model $Model, $settings = array()) {
	    if (!isset($this->settings[$Model->alias])) {

	    	$rulesSettings = $settings['rules'];

	        $this->settings[$Model->alias] = array(
	            'rules' => $rulesSettings,
	        );

			$this->GameRule = ClassRegistry::init('GameRule');
	        $rules = array();
	        for($i = 0; $i < count($rulesSettings); $i++)
	        {
		        $rule = array(
						'GameRule' => array(
							'model' => $Model->alias,
							'action' => $rulesSettings[$i]['action'],
							'points' => $rulesSettings[$i]['points'],
							'occurence' => $rulesSettings[$i]['occurence'],
							'badge_id' => 1
						)
					);
				if (!$this->GameRule->hasAny($rule["GameRule"])){
					array_push($rules,$rule);
				}
			}
			$this->GameRule->saveAll($rules);
	    }

	    $this->settings[$Model->alias] = array_merge(
	    	$this->settings[$Model->alias], (array)$settings
	    );
	}


	public function afterSave(Model $Model, $created, $options = Array()) {
			$action = 'Edit';
			if($created)
			{
				$action = 'Add';
			}
			$this->savePoints($Model,$action);
	}

	public function afterDelete(Model $Model) {
			$action = 'Delete';

			$this->savePoints($Model,$action);
	}

	function savePoints(Model $Model,$action)
	{
		$this->GameRule = ClassRegistry::init('GameRule');
		$this->GamePoint = ClassRegistry::init('GamePoint');

		$optionRules = array('conditions' => array(
			'GameRule.model = \''.$Model->alias.'\'',
			'GameRule.action = \''.$action.'\''
		));
		$rules = $this->GameRule->find('first', $optionRules);

		if($rules) {
			if ($action == 'Edit' && !$Model->data[$Model->name]['is_active']) {
				$rules['GameRule']['points'] = -$rules['GameRule']['points'];
			} else {
				$rules['GameRule']['points'] = 0;
			}
			$points = array(
				'GamePoint' => array(
					'user_id' => $Model->data[$Model->name]['profile_id'],
					'rule_id' => $rules['GameRule']['id'],
					'foreign_key' => $Model->data[$Model->alias]['id'],
					'points' => $rules['GameRule']['points'],
					'badge_id' => $rules['GameRule']['badge_id']
				)
			);
			$this->GamePoint->save($points);
		}
	}
}


?>
