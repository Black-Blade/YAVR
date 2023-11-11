# YAVR
   Mit diesem Modul ist es möglich die Geräte von Yamaha Endstufen in IP-Symcon einzubinden.

## Inhaltverzeichnis
- [YAVR](yavr)
  - [Inhaltverzeichnis](#inhaltverzeichnis)
  - [1. Voraussetzungen](#1-voraussetzungen)
  - [2. Einstellungen](#2-einstellungen)
  - [3. Profile](#3-profile)
  - [4. SetPower](#4-setpower)
  - [5. SetSeepMode](#5-setseepmode)
  - [6. SetSoundProgram](#6-setsoundprogram)
  - [7. SetInput](#7-setinput)
  - [8. SetMute](#8-setmute)
  - [9. SetVolume](#9-setvolume)
  - [10. SetPlayback](#10-setplayback)
  - [11. SetRepeat](#11-setrepeat)
  - [12. SetShuffle](#12-setshuffle)
  - [13. SetSurroundAI](#13-setsurroundai)
  - [14. SetSurrDecoder](#14-setsurrdecoder)
  - [15. SetCursor](#15-setcursor)
  - [16. SetCursorControl](#16-setcursorcontrol)
  - [17. SetPartyMode](#17-setpartymode)
  - [18. SetRadioband](#18-setradioband)
  - [19. SetPreset](#19-setpreset)
  - [20. SetEnhancer](#20-setenhancer)

## 1. Voraussetzungen
* mindestens IPS Version 6.0
* Yamaha A/V-Receiver mit Netzwerkschnittstelle

## 2.  Einstellungen

* **Host**  _Der Hostname bzw. die IP-Adresse_
* **Zone**  _Welche Zone mit dieser Instanz gesteuert werden soll_
* **Interval**  _In welchem Abstand soll der Status abgeglichen werden_


## 3. Profile
Es werden bei der Installation folgende Listen erstellt, diese sind notwendig damit der A/V-Reciver richtig eingestellt werden kann

Jedes Profil fängt immer mit der YAVR_"InstanceID"_"Zone"_ an <br>
z.B. YAVR_16527_Main_Zone_*


 |Profie            |Beschreibung
|:-----------------:|:-------------------------------------------------------:|
|input              |Steuernung des Eingangs
|sound_program_list |Ändern des Klangbilds
|sound_power_list   |Ändert den Einschaltezustand (Toggel,Standby,ON)
|surr_decoder_list  |Ändert den Decoder
|cursor             |Steuerung des A/V-Recivers (Up,Down,Left,Right,Return,Select,Home) gilt auch für HDMI (CMC-Kommandos)
|cursor_control     |Steuerung des A/V-Recivers (Home,Red,Blue, usw.)
|sleep              |Ändern des Sleepmode 0,30,60,90,120 Minuten
|_radioband         |Ändern des Frquenzbands (AM,FM,DAB) wird nicht jedes bei allen A/V-Reciver benuztz!


## 4. SetPower

Value = Profiel (YAVR_"InstanceID"_"Zone"_)_sound_power_list

`RequestAction(Ident,Value)`<br>
`YAVR_SetPower(InstanceID,Value)` <br>

Schalte den A/V Reciver in diesen Powermode

## 5. SetSleepMode

Value = Profil (YAVR_"InstanceID"_"Zone"_)_sleep

`RequestAction(Ident,Value)` <br>
`YAVR_SetSleep(InstanceID,Value)`<br>

Schalte den A/V Reciver in diesen Sleepmode


## 6. SetSoundProgram

Value = Profiel (YAVR_"InstanceID"_"Zone"_)_sound_program_list

`RequestAction(Ident,Value)` <br>
`YAVR_SetSoundProgram(InstanceID,Value)`<br>

Schalte den A/V Reciver in diesen Klangmodus

## 7. SetInput

Value = Profiel (YAVR_"InstanceID"_"Zone"_)_input

`RequestAction(Ident,Value)` <br>
`YAVR_SetInput(InstanceID,Value)`<br>

Schalte den A/V Reciver auf diesen Eingag

## 8. SetMute

Value = true,false

`RequestAction(Ident,Value)` <br>
`YAVR_SetMute(InstanceID,Value)`<br>

Schalte den A/V Reciver auf Mute oder wieder aus

## 9. SetVolume

Value = 0 bis 100

`RequestAction(Ident,Value)` <br>
`YAVR_SetVolume(InstanceID,Value)`<br>

Schalte den A/V Reciver die gewünschte Lautstärke


## 10. SetPlayback

Value = PlaybackPreviousNext

`RequestAction(Ident,Value)` <br>
`YAVR_SetPlayback(InstanceID,Value)`<br>

Schalte den A/V Reciver nach PlaybackPreviousNext

## 11. SetRepeat

Value = true,false

`RequestAction(Ident,Value)` <br>
`YAVR_SetRepeat(InstanceID,Value)`<br>

Schalte den A/V Reciver auf diese Repeat


## 12. SetShuffle

Value = true,false

`RequestAction(Ident,Value)` <br>
`YAVR_SetShuffle(InstanceID,Value)`<br>

Schalte den A/V Reciver auf diese SetShuffle

## 13. SetSurroundAI

Value = true,false

`RequestAction(Ident,Value)` <br>
`YAVR_SetSurround_AI(InstanceID,Value)`<br>

Schalte den A/V Reciver auf diese Surround_AI

## 14. SetSurrDecoder

Value = Profiel (YAVR_"InstanceID"_"Zone"_)_surr_decoder_list

`RequestAction(Ident,Value)` <br>
`YAVR_SetSurr_Decoder(InstanceID,Value)`<br>

Schalte den A/V Reciver auf diesen Surround Decoder

## 15. SetCursor

Value = Profiel (YAVR_"InstanceID"_"Zone"_)_cursor

`RequestAction(Ident,Value)` <br>
`YAVR_SetCursor(InstanceID,Value)`<br>

Schalte die Tasten am A/V Reciver 

## 16. SetCursorControl

Value = Profiel (YAVR_"InstanceID"_"Zone"_)_cursor_control

`RequestAction(Ident,Value)` <br>
`YAVR_SetCursor_Control(InstanceID,Value)`<br>

Schalte die Tasten am A/V Reciver 

## 17. SetPartyMode

Value = true,false

`RequestAction(Ident,Value)` <br>
`YAVR_SetPartyMode(InstanceID,Value)`<br>

Schaltet den A/V Reciver in den Party Modus


## 18. SetRadioband

Value = Profiel (YAVR_"InstanceID"_"Zone"_)_radioband

`RequestAction(Ident,Value)` <br>
`YAVR_SetRadioband(InstanceID,Value)`<br>

Schalte den am A/V Reciver das Frequenzband (AM,FM,DAB)

## 19. SetPreset

Value = 1 bis 10

`RequestAction(Ident,Value)` <br>
`YAVR_SetPreset(InstanceID,Value)`<br>

Schaltet den A/V Reciver im Radiomode auf diesen Sender

## 20. SetEnhancer

Value = true,false

`RequestAction(Ident,Value)` <br>
`YAVR_SetEnhancer(InstanceID,Value)`<br>

Schaltet den A/V Reciver in den Enhancer mode