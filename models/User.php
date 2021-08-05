<?php

namespace app\models;

use yii\db\ActiveRecord;

class User extends ActiveRecord implements \yii\web\IdentityInterface
{


  public static function tableName()
  {
      return 'person';
  }


    /**
     * {@inheritdoc}
     */
    public static function findIdentity($id)
    {
        return static::findOne($id);
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
//        return static::findOne(['access_token' => $token]);
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        return static::findOne(['login' => $username]);
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthKey()
    {
//        return $this->authKey;
//        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function validateAuthKey($authKey)
    {
//        return $this->auth_key === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
//    public function validatePassword($password,$md5password)
//    {
//        if($md5password === md5($password)){
//            return true;
//        }else{
//            return false;
//        }
//    }

    public function validatePassword($password)
    {
//        var_dump(md5($password));
//        var_dump($this->password);
//        die();

        return $this->password === md5($password);
    }


//    public function generateAuthKey(){ // генерация случайной строки
//        $this->authKey = \Yii::$app->security->generateRandomString();
//    }
}
