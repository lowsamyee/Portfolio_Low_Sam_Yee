package model.sound;

import javax.sound.sampled.*;

import controller.GameController;

import java.io.InputStream;

public class Sound {
    private GameController controller;
    private Clip move;
    private Clip notify;
    private Clip capture;
    private Clip clip;

    public Sound(GameController controller) {
        this.controller = controller;
        try {
            this.move = AudioSystem.getClip();
            this.notify = AudioSystem.getClip();
            this.capture = AudioSystem.getClip();

            initializeClip(getClass().getResourceAsStream("move.wav"), move);
            initializeClip(getClass().getResourceAsStream("notify.wav"), notify);
            initializeClip(getClass().getResourceAsStream("capture.wav"), capture);
        } catch (LineUnavailableException e) {
            e.printStackTrace();
        }
    }

    public void soundMove() {
        if (!isMuted()) { // Check if sound is muted before playing
            play(move);
        }
    }

    public void soundNotify() {
        if (!isMuted()) { // Check if sound is muted before playing
            play(notify);
        }
    }

    public void soundCapture() {
        if (!isMuted()) { // Check if sound is muted before playing
            play(capture);
        }
    }

    public void toggleMute(boolean mute) {

        if (mute) {
            controller.setMute(mute);
            System.out.println("Mute toggled. isMuted: " + isMuted());

        } else {
            System.out.println("Unmuted: ready to play sounds.");
            controller.setMute(!mute);

        }
    }

    public boolean isMuted() {
        return controller.getMuteStatus();
    }

    public void unmute() {
        if (!isMuted() && clip != null) {
            clip.setFramePosition(0); // Reset to the start
            clip.start();
        }
    }

    public void stop() {
        if (clip != null) {
            clip.stop();
        }
    }

    private void initializeClip(InputStream soundStream, Clip clip) {
        try {
            AudioInputStream audioInputStream = AudioSystem.getAudioInputStream(soundStream);
            clip.open(audioInputStream);
        } catch (Exception e) {
            e.printStackTrace();
        }
    }

    public void play(Clip clip) {
        if (clip != null && !isMuted()) {
            clip.setFramePosition(0); // Reset to the start
            clip.start();
        } else {
            System.out.println("Sound playback skipped (muted or null).");
        }
    }
}