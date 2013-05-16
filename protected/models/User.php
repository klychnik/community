<?php

/**
 * This is the model class for table "User".
 *
 * The followings are the available columns in table 'User':
 * @property integer $id
 * @property string $first_name
 * @property string $second_name
 * @property string $last_name
 * @property string $email
 * @property string $phone
 * @property string $photo
 * @property string $website
 * @property string $password
 */
class User extends CActiveRecord
{
	const SIGNUP = 'signup';
	const LOGIN = 'login';
	const PROFILE = 'profile';
	const PASSWORD = 'password';
	
	public $password_repeat;
		
	/**
	 * Returns the static model of the specified AR class.
	 * @return User the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'users';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('first_name, last_name, email, phone, password, password_repeat', 'required', 'on'=>self::SIGNUP, 'message'=>'Заполните поле {attribute}'),
			array('first_name, last_name, second_name, zip, address, city, email, phone, website', 'required', 'on'=>self::PROFILE, 'message'=>'Заполните поле {attribute}'),
			array('email, password', 'required', 'on'=>self::LOGIN, 'message'=>'Заполните поле {attribute}'),
			array('email, phone', 'unique', 'on'=>self::SIGNUP, 'message'=>'Такой {attribute} уже зарегистрирован'),
			array('email, password', 'length', 'max'=>255, 'on'=>self::SIGNUP, 'message'=>'Максимальная длина {attribute} 255 символов'),
			array('phone', 'length', 'max'=>20, 'on'=>self::SIGNUP, 'message'=>'{attribute} должен состоять не более 20 цифр'),
			array('password', 'compare', 'compareAttribute'=>'password_repeat', 'on'=>self::SIGNUP, 'message'=>'Пароли не совпадают'),
			array('id, email, phone, password', 'safe', 'on'=>'search'),
			array('email', 'filter', 'filter'=>'mb_strtolower', 'on'=>self::SIGNUP, 'message'=>'{attribute} должен быть записан маленькими буквами'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'Id',
			'first_name' => 'Имя',
			'second_name' => 'Отчество',
			'last_name' => 'Фамилия',
			'email' => 'Email',
			'phone' => 'Телефон',
			'website' => 'Сайт',
			'password' => 'Пароль',
			'password_repeat' => 'Повторите пароль',
			'photo' => 'Фотография',	
			'address' => 'улица, дом, квартира',
			'city' => 'город',
			'zip' => 'почтовый индекс',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id);

		$criteria->compare('last_name',$this->first_name,true);
		$criteria->compare('first_name',$this->last_name,true);
		$criteria->compare('email',$this->email,true);

		$criteria->compare('phone',$this->phone,true);

		$criteria->compare('password',$this->password,true);

		return new CActiveDataProvider('User', array(
			'criteria'=>$criteria,
		));
	}

	protected function beforeSave()
	{
		if(parent::beforeSave())
		{
			if($this->isNewRecord)
			{
				$this->registered = date('Y-m-d H:i:s');
                $this->password = self::hashPassword($this->password);
			}
			
			if($this->scenario == self::PASSWORD)
			{
				$this->password = self::hashPassword($this->password);
			}
	
			return true;
		}
	
		return false;
	}
	
	static function hashPassword($password)
	{
		return md5($password);
	}
	
	public function login()
	{
		$identity = new UserIdentity($this->email, $this->password);
		if($identity->authenticate()) {
			Yii::app()->user->login($identity);
			return true;
		} else {
			$this->addError('email', 'Не верный email или пароль');
			return false;
		}
	}
}