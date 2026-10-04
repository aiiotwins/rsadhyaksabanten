<?php

declare(strict_types=1);

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;
use yii\caching\TagDependency;

class User extends ActiveRecord implements IdentityInterface
{
    public $password;
    
    const STATUS_DELETED = 0;
    const STATUS_INACTIVE = 9;
    const STATUS_ACTIVE = 10;

    public static function tableName()
    {
        return '{{%user}}';
    }

    public static function getUserCacheTag($userId)
    {
        return 'user_tag_' . $userId;
    }

    public static function findIdentity($id)
    {
        $cacheKey = 'user_identity_' . $id;

        return Yii::$app->cache->getOrSet(
            $cacheKey,
            function () use ($id) {
                return static::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]);
            },
            3600,
            // Ikat cache ini ke tag spesifik user ID tersebut
            new TagDependency(['tags' => self::getUserCacheTag($id)])
        );
    }

    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['auth_key' => $token, 'status' => self::STATUS_ACTIVE]);
    }

    public static function findByUsername($username)
    {
        $cleanUsername = strtolower(trim((string)$username));
        $cacheKey = 'user_username_' . md5($cleanUsername);

        // Coba ambil dari cache terlebih dahulu
        $user = Yii::$app->cache->get($cacheKey);
        if ($user !== false) {
            return $user;
        }

        // Jika tidak ada di cache, query ke database
        $user = static::findOne([
            'username' => $cleanUsername,
            'status' => self::STATUS_ACTIVE,
        ]);

        // Jika user ditemukan di database, simpan ke cache bersama TagDependency
        if ($user !== null) {
            Yii::$app->cache->set(
                $cacheKey,
                $user,
                3600,
                new TagDependency(['tags' => self::getUserCacheTag($user->id)])
            );
        }

        return $user;
    }

    public function getId()
    {
        return $this->getPrimaryKey();
    }

    public function getAuthKey()
    {
        return $this->auth_key;
    }

    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    public function getPasswordHash()
    {
        return $this->password_hash;
    }

    /**
     * Otomatis invalidate tag ketika data user diubah/diupdate
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        $this->invalidateCache();
    }

    /**
     * Otomatis invalidate tag ketika user dihapus
     */
    public function afterDelete()
    {
        parent::afterDelete();
        $this->invalidateCache();
    }

    /**
     * Menghapus semua cache yang terikat dengan user ini
     */
    public function invalidateCache()
    {
        TagDependency::invalidate(Yii::$app->cache, self::getUserCacheTag($this->id));
    }
}