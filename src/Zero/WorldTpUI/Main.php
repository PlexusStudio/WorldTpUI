<?php

declare(strict_types=1);

namespace Zero\WorldTpUI;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use Zero\WorldTpUI\Command\wtpuiCommand;
use Zero\WorldTpUI\UI\CustomUI;
use Zero\WorldTpUI\UI\ListenerUI;

class Main extends PluginBase{

    public $ui = [];
    public $id = [];
    private $config;

    public function onEnable() : void{
        if($this->getServer()->getName() === 'PocketMine-MP'){
            if($this->isFirstLoad() === true){
                $this->getLogger()->info(TextFormat::YELLOW . "\nHello and Welcone to WorldTpUI\nEdit the config in 'plugins/WorldTpUI/config.yml'");
            }else{
                $this->getLogger()->info(TextFormat::YELLOW . "is Loading...");
                $this->checkConfigVersion();
                $this->config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
                if($this->config->get("load_all_worlds") === true){
                    $this->loadAllWorlds();
                }
                $this->createWorldUI();
                $this->getServer()->getPluginManager()->registerEvents(new ListenerUI($this), $this);
                $this->getServer()->getCommandMap()->register('wtpui', new wtpuiCommand($this));
                $this->getLogger()->info(TextFormat::GREEN . "Everything has Loaded!");
            }
        }else{
            $this->getLogger()->info(TextFormat::RED . 'Sorry this plugin does not support Spoons');
            $this->getServer()->getPluginManager()->disablePlugin($this);
        }
    }

    public function isFirstLoad() : bool{
        if(is_file($this->getDataFolder() . "config.yml")){
            return false;
        }else{
            @mkdir($this->getDataFolder());
            $config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
            $config->setAll(array('version' => $this->getDescription()->getVersion(), "load_all_worlds" => false));
            $config->save();
            return true;
        }
    }

    public function loadAllWorlds() : void{
        $worlds = $this->getServer()->getDataPath() . "worlds/";
        $allWorlds = array_slice(scandir($worlds), 2);
        foreach($allWorlds as $world){
            if(is_dir($this->getServer()->getDataPath() . 'worlds/' . $world . '/')){
                $this->getServer()->loadLevel($world);
            }
        }
    }

    public function createWorldUI() : void{
        $id = $this->getRandId();
        $ui = new CustomUI($id);
        $this->ui['world-tp'] = $ui;
    }

    public function checkConfigVersion() : void{
        if(isset($this->config)){
            $this->config->getAll();
        }else{
            $this->config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
            $this->config->getAll();
        }
        if($this->getDescription()->getVersion() != $this->config->get('version')){
            $this->getLogger()->info(TextFormat::YELLOW . 'Config is not update-to-date');
            $this->config->set('version', $this->getDescription()->getVersion());
            $this->config->save();
            //$this->setNewConfigItems();//soon
            $this->getLogger()->info(TextFormat::AQUA . 'Config is now update-to-date');
        }else{
            $this->getLogger()->info(TextFormat::AQUA . 'Your Config is update-to-date');
        }
    }

    public function getRandId() : int{
        $rand = rand(1, 1000);
        if(in_array($rand, $this->id)){
            return self::getRandId();
        }else{
            $this->id[] = $rand;
            return $rand;
        }
    }

    public function onDisable() : void{
        $this->getLogger()->info(TextFormat::RED . "unloading plugin...");
        if(isset($this->config)){
            $this->config->save();
        }
        $this->getLogger()->info(TextFormat::RED . "has Unloaded, Goodbye!");
    }
}