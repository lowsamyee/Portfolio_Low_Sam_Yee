/*
The GameController class manages game logic and interaction between the model and view.
Design Pattern: Acts as part of the MVC pattern, representing the Controller component.

1. Get piece by calling board.getPiece() method - Delegation and encapsulation used: controller does not directly interact with the pieces or the board's internal structure.
2. Compisition- The Board object is created as a part of GameController (via the constructor), Board is integral to the GameController and cannot exist independently without the GameController.
3. Aggregation- The TimerController, LogManager, Sound is created as a part of GameController.

Written by:
 - Kah Wei
 - Hui May
*/
package controller;

import model.*;
import model.sound.Sound;
import view.MainView;
import utility.*;

import java.awt.event.ActionEvent;
import java.awt.event.ActionListener;
import java.util.ArrayList;
import java.util.stream.Collectors;
import java.util.stream.IntStream;

public class GameController {
    private Board board; // The model
    private MainView mainView; // The main view
    private Sound sound;
    private final LogManager logManager; // Log manager
    private boolean isMuted = false; // Centralized mute state
    private ArrayList<int[]> availableMoves = new ArrayList<>();
    private String currentPlayer = "Blue";
    private String logMessage;
    private String errorMessage;
    private Piece selectedPiece = null;
    private boolean isMoveInProgress = false;
    private int turnCounter = 0;
    private TimerController timerController;

    // Kah Wei
    public GameController(Board board) {
        this.board = board;

        /*
         * LogManager, mainView and timerController act as delegator here,
         * while the objects inside those classes act as delegates.
         * 
         */
        // initialize log manager
        this.logManager = new LogManager();
        // Initialize views
        this.mainView = new MainView(this);
        this.timerController = new TimerController(mainView);

        sound = new Sound(this);
        mainView.display();

    }

    // Kah Wei: Handles clicks on cells, fetching moves for the selected piece and
    // highlighting them.
    private void handleCellClick(int x, int y) {
        selectedPiece = board.getPiece(x, y);

        // Get the available moves for the selected piece
        availableMoves = selectedPiece.getAvailableMoves(x, y, board);

        // Refactor: Filter moves containing enemy pieces using streams
        ArrayList<int[]> moveContainEnemy = availableMoves.stream()
                .filter(move -> {
                    int targetX = move[0];
                    int targetY = move[1];
                    Piece targetPiece = board.getPiece(targetX, targetY);
                    return targetPiece != null && !targetPiece.getColor().equalsIgnoreCase(currentPlayer);
                })
                .collect(Collectors.toCollection(ArrayList::new));

        // Highlight available moves and enemy-containing moves
        mainView.getBoardView().highlightAvailableMoves(availableMoves, moveContainEnemy);
        sound.soundMove();
    }

    // Hui May: Updates the game state, including flipping the board and switching
    // players.
    private void updateGameState(int x, int y, String logMessage) {
        mainView.updateStatus(logMessage);
        mainView.getBoardView().clearHighlights();

        // Trigger Tor/Xor transformation every 2 turns
        if (turnCounter >= 4 && turnCounter % 4 == 0) {
            torTransformation();
        }
        board.flipBoard();
        mainView.getBoardView().flipBoardView();

        // Rotate images for all pieces
        IntStream.range(0, 8).forEach(i -> IntStream.range(0, 5)
                .mapToObj(j -> board.getPiece(i, j))
                .filter(piece -> piece != null)
                .forEach(Piece::rotateImage));

        // Switch players
        currentPlayer = currentPlayer.equals("Blue") ? "Red" : "Blue";
        updateBoardView();
        mainView.updateStatus("Board flipped. It's now " + currentPlayer + "'s turn.");
        logManager.logAction(logMessage);
    }

    // Hui May: checking the selected piece is valid
    public boolean isSelectedPieceValidate(Piece selectedPiece) {
        if (selectedPiece == null || (!selectedPiece.getColor().equalsIgnoreCase(currentPlayer))) {
            sound.soundNotify();
            errorMessage = "Please select any " + currentPlayer + " piece to move.";
            mainView.updateStatus(errorMessage); // Update status label
            return false;
        }
        return true;
    }

    // Hui May: Update the board view
    private void updateBoardView() {
        for (int i = 0; i < 8; i++) {
            for (int j = 0; j < 5; j++) {
                Piece piece = board.getPiece(i, j);
                mainView.getBoardView().updateCell(i, j, piece);

            }
        }
    }

    /*
     * Kah Wei: Template method used, it defines the overall structure
     * or sequence of steps required to start the game while delegating specific
     * tasks
     */
    public void startGame() {
        timerController.startTimer();
        logManager.initializeSaveFile();
        board.initialize(); // Call initialize() to set up the board board.
        attachBoardListener();
        updateBoardView();

    }

    // Hui May: Format coordinate to allow user to easily recognize in board
    public String formatCoordinate(int x, int y) {
        char columnLetter = (char) ('a' + y); // Adjust
        // column for flipped board
        int rowNumber = x + 1; // Adjust row number for flipped board
        return "(" + rowNumber + ", " + columnLetter + ")";
    }

    // Kah Wei: Handles the move of the selected piece, check does the piece select
    // the highlight cell for the movement
    public void logMove(int boardX, int boardY) {
        for (int[] move : availableMoves) {
            if (move[0] == boardX && move[1] == boardY) {
                logMessage = selectedPiece.getColor() + " " + selectedPiece.getName() + " move from "
                        + formatCoordinate(selectedPiece.getX(), selectedPiece.getY())
                        + " to " + formatCoordinate(boardX, boardY);
                board.movePiece(boardX, boardY, selectedPiece);
                if (board.isPieceCapture()) {
                    logMessage += " and captured " + board.getCapturePiece().getColor() + " "
                            + board.getCapturePiece().getName() + " at "
                            + formatCoordinate(board.getCapturePiece().getX(), board.getCapturePiece().getY());
                }
                sound.soundMove();
                turnCounter++;
                updateGameState(boardX, boardY, logMessage);
                isMoveInProgress = false; // Mark the move as completed
                return; // Exit to prevent further execution
            }
        }
    }

    // Kah Wei: Handles the transformation of Tor and Xor every 2 turns
    public void torTransformation() {
        // Perform transformation every 2 turns
        if (turnCounter % 4 == 0) {
            board.transformTorXor();

            // Update the selected piece reference if it was transformed
            if (selectedPiece != null) {
                int x = selectedPiece.getX();
                int y = selectedPiece.getY();
                selectedPiece = board.getPiece(x, y);
            }
        }
    }

    // Kah Wei: Attaches listeners to the board cells, for user interaction
    public void attachBoardListener() {

        // Attach listeners to the board cells
        for (int i = 0; i < 8; i++) {
            for (int j = 0; j < 5; j++) {
                final int x = i;
                final int y = j;

                mainView.getBoardView().addCellListener(x, y, new ActionListener() {
                    @Override
                    public void actionPerformed(ActionEvent e) {
                        // Map view coordinates to the correct board coordinates
                        int[] mappedCoords = board.mapViewToBoardCoordinates(x, y);
                        int boardX = mappedCoords[0];
                        int boardY = mappedCoords[1];

                        // Check if a valid piece is selected for the current player
                        Piece pieceAtCell = board.getPiece(boardX, boardY);

                        if (isSelectedPieceValidate(pieceAtCell) && !isMoveInProgress) {
                            // Handle piece selection
                            handleCellClick(boardX, boardY);

                        } else {
                            isMoveInProgress = true;
                            logMove(boardX, boardY);
                            checkGameEnd();
                        }
                        isMoveInProgress = false;
                        return;
                    }
                });
            }

        }
        return;

    }

    // hui may: checking the status of the Sau Piece
    private void checkGameEnd() {

        // Check if any Blue Sau piece is alive
        boolean isBlueSauAlive = IntStream.range(0, board.getRows())
                .anyMatch(i -> IntStream.range(0, board.getCols())
                        .mapToObj(j -> board.getPiece(i, j))
                        .filter(piece -> piece instanceof Sau)
                        .anyMatch(piece -> piece.getColor().equalsIgnoreCase("blue")));

        // Check if any Red Sau piece is alive
        boolean isRedSauAlive = IntStream.range(0, board.getRows())
                .anyMatch(i -> IntStream.range(0, board.getCols())
                        .mapToObj(j -> board.getPiece(i, j))
                        .filter(piece -> piece instanceof Sau)
                        .anyMatch(piece -> piece.getColor().equalsIgnoreCase("red")));

        // Determine game-ending conditions
        if (!isBlueSauAlive) {
            endGame("Red"); // Red wins
        } else if (!isRedSauAlive) {
            endGame("Blue"); // Blue wins
        }

    }

    // Kah Wei: Handles the end of the game, showing winning view
    private void endGame(String winingPlayer) {
        // Disable further moves or reset the game
        mainView.showWinningView(winingPlayer);
    }

    // kah wei: mute sound
    public void setMute(boolean mute) {
        isMuted = mute;
        String status = isMuted ? "Sound muted." : "Sound unmuted.";
        mainView.updateStatus(status);
        mainView.updateSoundIcon(mute);
    }

    // kah wei: encapsulation - get the mute status
    public boolean getMuteStatus() {
        return isMuted;
    }

    // kah wei: resume game, delegate to timer
    public void resumeGame() {
        timerController.startTimer();
        mainView.updateStatus("Game resumed!"); // Optionally update the status label
    }

    // kah wei: pause game, delegation to TimeController
    public void pauseGame() {
        timerController.stopTimer();
    }

    // hui may: Template method used for reset the game
    public void resetGame() {
        timerController.resetTimer();
        currentPlayer = "Blue"; // Reset the player to the initial player
        turnCounter = 0;
        if (board.isFlipped()) {
            board.flipBoard(); // Ensure the model's flip state is reset
            mainView.getBoardView().flipBoardView(); // Reset the UI to unflipped state
        }
        board.initialize(); // Reinitialize the board
        mainView.getBoardView().clearHighlights(); // Clear any lingering highlights
        updateBoardView(); // Update the board view to reflect the new state
        logManager.initializeSaveFile(); // Optionally reset logs
        mainView.updateStatus("Board has been reset."); // Update the status
        System.out.println("Log reseted.");
    }

    // Kah Wei: Template method used for stop the game
    public void stopGame() {
        mainView.getFrame().setVisible(false); // Hides the current game view
        timerController.stopTimer();
        board.initialize();
        currentPlayer = "Blue"; // Reset the player to the initial player
        mainView.getBoardView().initialBoard();
        GameController newGameController = new GameController(board);
        newGameController.mainView.getFrame().setVisible(true); // Show the new game
        logManager.initializeSaveFile(); // Optionally reset logs
        mainView.updateStatus("Game stopped!");
    }

}