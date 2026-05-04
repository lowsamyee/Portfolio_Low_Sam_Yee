/*
 * The GameLauncher class serves as the entry point for the application.
 * 
 * Responsibilities:
 * - Launches the game by creating an instance of the GameController, 
 *   which manages the game's logic and interaction between the model and view.
 *   
 *   Written by: Low Sam Yee
 */
import model.Board;

import controller.GameController;

public class GameLauncher {
    public static void main(String[] args) {
        // Initialize the model and start the game
        Board board = new Board();
        new GameController(board);
    }
}
