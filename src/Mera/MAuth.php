<?php

namespace Mera;

/* Eklenti Ryuga tarafından kodlanmıştır
*/
use pocketmine\plugin\PluginBase;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\event\Listener;
use pocketmine\utils\Config;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerPreLoginEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\command\{Command, CommandSender};


class MAuth extends PluginBase implements Listener{

	public function onEnable(){
	    $this->getLogger()->info("Eklenti Aktif Auth ---");
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$cfg = new Config($this->getDataFolder()."config.yml", Config::YAML);
		$this->getLogger()->warning("Eklenti Yüklendi!- Çalmak yasaktır");
	}
	public function oyuncuKontrol(PlayerPreLoginEvent $e){
        $o = $e->getPlayer();
        foreach($this->getServer()->getOnlinePlayers() as $ol){
            if($o->getName() == $ol->getName()){
                if($this->girissorgula($ol)){
                    $e->setCancelled();
                    $o->kick("§l§7» §cOyuncu oyunda...", false);
                    $ol->sendMessage("§l§7^^ §cAz önce biri hesabına girmeye çalıştı!");
                    $ol->sendMessage("Koruma devreye girdi ve girmeye çalışan kişi atıldı!");
                }
            }
        }
    }
    public function girissorgula($e){
        if(empty($this->e[$e->getName()])){
            return true;
        }else{
            return false;
        }
    }
    	
	public function onCommand(CommandSender $oyuncu, Command $kmt, string $lbl, array $args): bool{
	    if($kmt->getName() == "sifre"){
	    $this->sifreForm($oyuncu);
	    }
	    return true;
	}
	public function sifreForm(Player $oyuncu){
	$f = $this->getServer()->getPluginManager()->getPlugin("FormAPI")->createCustomForm(function (Player $oyuncu, $data){
	    if($data[0] === null){
	        return true;
	    }
	    if($data[1] != $data[2]){
					$oyuncu->sendMessage("§cŞifre uyumsuz!");
					return;
				}
				$cfg = new Config($this->getDataFolder().$oyuncu->getName().".yml", Config::YAML);

				$encrypt_method = 'AES-256-CBC';
				$secret_key = '11*_33';
				$secret_iv = '22-=**_';
				$cozum = hash('sha256', $secret_key);
				$iv = substr(hash('sha256', $secret_iv), 0, 16);
				$sifrelendi = openssl_encrypt($data[1],$encrypt_method, $cozum, false, $iv);
				$pass = $cfg->get("Sifre");
				$notsifrelenme = openssl_decrypt($pass,$encrypt_method, $cozum, false, $iv);
                if($notsifrelenme != $data[0]){
					$oyuncu->sendMessage("§cX §7Eski sifreni hatirlamiyorsan kuruculara ulaş :)");
				return;
				}
				$cfg->set("Sifre", $sifrelendi);
				$cfg->save();
				$oyuncu->sendPopup("§aŞifre değiştirildi!");

			});
			$f->setTitle("Sifre");
			$f->addInput("Eski Şifre");
			$f->addInput("Yeni Şifre");
			$f->addInput("Yeni Şifre Tekrar");
			$f->sendToPlayer($oyuncu);
	}
public function eventJoin(PlayerJoinEvent $event){
    $oyuncu = $event->getPlayer();
    unset($this->login[$event->getPlayer()->getName()]);
		if(file_exists($this->getDataFolder().$event->getPlayer()->getName().".yml")){
			$this->girisForm($event->getPlayer());
			//yeni ise(Kayit Yoksa)
		}else{
			$this->kayitForm($event->getPlayer());
		}
}
}
public function girisForm(Player $oyuncu):void {
$f = $this->getServer()->getPluginManager()->getPlugin("FormAPI")->createCustomForm(function (Player $oyuncu, $data){
    if($data[0] === null){
        			$this->girisForm($oyuncu);
				return;
			}
             $cfg = new Config($this->getDataFolder().$oyuncu->getName().".yml", Config::YAML);
			 $pass = $cfg->get("Sifre");
			$encrypt_method = 'AES-256-CBC';
			$secret_key = '11*_33';
			$secret_iv = '22-=**_';
			$cozum = hash('sha256', $secret_key);
			$iv = substr(hash('sha256', $secret_iv), 0, 16);
			$notsifrelenme = openssl_decrypt($pass,$encrypt_method, $cozum, false, $iv);
			if($data[0] == $notsifrelenme){
				$oyuncu->sendPopup("§7[§a+§7]");
				$this->login[$oyuncu->getName()] = true;
			}else{
				$oyuncu->kick("§cŞifre yanlış");
				$this->login[$oyuncu->getName()] = true;
			}
		});
		$f->setTitle("Giriş");
		$f->addInput("Şifre");
		$f->sendToPlayer($oyuncu);
	}
	public function kayitForm(Player $oyuncu): void {
	$f = $this->getServer()->getPluginManager()->getPlugin("FormAPI")->createCustomForm(function (Player $oyuncu, $data){
    if($data[0] === null){
				$this->kayitForm($oyuncu);
				return;
			}
			if($data[0] != $data[1]){
				$oyuncu->kick("§cŞifreler eşleşmiyor");
				$this->login[$oyuncu->getName()] = true;

				return;
			}
			$encrypt_method = 'AES-256-CBC';
			$secret_key = '11*_33';
			$secret_iv = '22-=**_';
			$cozum = hash('sha256', $secret_key);
			$iv = substr(hash('sha256', $secret_iv), 0, 16);
			$sifrelendi = openssl_encrypt($data[0],$encrypt_method, $cozum, false, $iv);
			$cfg = new Config($this->getDataFolder().$oyuncu->getName().".yml", Config::YAML);
            $cfg->set("Sifre", $sifrelendi);
            $cfg->save();
            $oyuncu->sendMessage("Başarıyla kayıt oldun.");
			$this->login[$oyuncu->getName()] = true;

		});
		$f->setTitle("Kayit");
		$f->addInput("Şifre");
		$f->addInput("Şifre Tekrar");
		$f->sendToPlayer($oyuncu);
	}
}
