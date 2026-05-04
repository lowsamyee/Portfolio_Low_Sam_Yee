/*
 * The Stopwatch class provides a simple timer utility that counts elapsed time in seconds. 
 * It notifies a listener about time updates at regular intervals.
 * 
 *  Design Details:
 * 
 *   -Uses the Swing `Timer` to trigger updates every second.
 *   -Implements a listener interface for external notification of time changes.
 *
 * Responsibilities:
 * 
 *   -Tracks elapsed time in seconds.
 *   -Starts, stops, and resets the timer.
 *   -Notifies an external listener about time updates.
 *
 *   
 *   Written by:Hoo Enn Xin
 * 
 */
package utility;

import javax.swing.Timer;
import java.awt.event.ActionEvent;
import java.awt.event.ActionListener;

public class Stopwatch {
    private Timer timer;
    private int seconds;
    private boolean isRunning;
    private GameTimerListener listener;

    // Listener Interface
    public interface GameTimerListener {
        void onTimeUpdate(int seconds);
    }

    // Constructor
    public Stopwatch(GameTimerListener listener) {
        this.listener = listener;
        this.seconds = 0;
        this.isRunning = false;

        // Initialize Timer
        this.timer = new Timer(1000, new ActionListener() {
            @Override
            public void actionPerformed(ActionEvent e) {
                seconds++;
                if (listener != null) {
                    listener.onTimeUpdate(seconds); // Notify listener of time update
                }
            }
        });
    }

    // Start the timer
    public void start() {
        if (!isRunning) {
            isRunning = true;
            timer.start();
        }
    }

    // Stop the timer
    public void stop() {
        if (isRunning) {
            isRunning = false;
            timer.stop();
        }
    }

    // Reset the timer
    public void reset() {
        stop();
        seconds = 0;
        if (listener != null) {
            listener.onTimeUpdate(seconds); // Notify listener of reset
        }
    }

    // Get elapsed time in seconds
    public int getElapsedTime() {
        return seconds;
    }
}
