/*
 * The TimerController class manages the game's timer functionality.
 * 
 * Responsibilities:
 * - Starts, stops, and resets the game timer.
 * - Updates the user interface through the {@link MainView} whenever the timer updates.
 * 
 * Design Highlights:
 * - Implements the {@link GameTimerListener} interface to receive time updates from the {@link Stopwatch} utility.
 * - Keeps the timer logic separate from the view, adhering to the MVC pattern.
 * 
 * Written by: Hoo Enn Xin
 */

package controller;

import utility.Stopwatch;
import utility.Stopwatch.GameTimerListener;
import view.MainView;

public class TimerController implements GameTimerListener {
    private Stopwatch stopwatch;
    private MainView mainView; // The main view

    public TimerController(MainView mainView) {
        this.mainView = mainView; // Initialize mainView

        stopwatch = new Stopwatch(this);
    }

    // Stops the timer
    public void stopTimer() {
        stopwatch.stop();
    }

    // Method from the GameTimerListener interface to update the view
    @Override
    public void onTimeUpdate(int seconds) {
        // Inform the view to update the time label with the current seconds
        mainView.updateTimeLabel(seconds);
    }

    public void startTimer() {
        stopwatch.start();
    }

    public void resetTimer() {
        stopwatch.reset();
        stopwatch.start();
    }
}
