/*
 * The LogManager class handles logging game actions and saving game progress.
 * 
 * Responsibilities:
 *   -Initialize and manage a log file for recording game events.
 *   -Save the game's log, along with a winning player's name, to a user-specified file.
 *   -Provide methods for logging individual actions during the game.
 *   
 *  Functions:
 *   -Uses a text file `game_log.txt` to record logs persistently.
 *   -Utilizes `JFileChooser` for user-friendly file saving.
 *   -Implements error handling to ensure robustness.
 *   
 *   Written by: Low Sam Yee
 */
package utility;

import java.io.BufferedReader;
import java.io.BufferedWriter;
import java.io.File;
import java.io.FileReader;
import java.io.FileWriter;
import java.io.IOException;

import javax.swing.JFileChooser;
import javax.swing.JOptionPane;

public class LogManager {
    // for submittion rmb chaneg to this!
    private static final String SAVE_FILE_PATH = "game_log.txt";

    public void initializeSaveFile() {
        File saveFile = new File(SAVE_FILE_PATH);
        try {
            if (!saveFile.exists())
                saveFile.createNewFile();
            System.out.println("Game saved to: " + saveFile.getAbsolutePath());

            new FileWriter(saveFile, false).close(); // Clear the file
        } catch (IOException e) {
            e.printStackTrace();
            // System.err.println("Error creating or clearing the save file: " +
            // e.getMessage());
        }
    }

    public void logAction(String logMessage) {
        try (BufferedWriter writer = new BufferedWriter(new FileWriter(SAVE_FILE_PATH, true))) {
            writer.write(logMessage);
            writer.newLine();
        } catch (IOException e) {
            e.printStackTrace();
        }
    }

    public void saveGame(String winningPlayer) {
        // Open the file save dialog to prompt the user where to save the file
        JFileChooser fileChooser = new JFileChooser();
        fileChooser.setDialogTitle("Save Game");

        // Optionally, set file filters
        fileChooser.setAcceptAllFileFilterUsed(false);
        fileChooser.addChoosableFileFilter(new javax.swing.filechooser.FileNameExtensionFilter("Text Files", "txt"));

        int result = fileChooser.showSaveDialog(null); // Open save dialog

        if (result == JFileChooser.APPROVE_OPTION) {
            File selectedFile = fileChooser.getSelectedFile();
            String filePath = selectedFile.getAbsolutePath();
            if (!filePath.endsWith(".txt")) {
                filePath += ".txt"; // Add file extension if not specified
            }

            // Read content from the log file (SAVE_FILE_PATH) and write to the selected
            // file
            try (BufferedReader reader = new BufferedReader(new FileReader(SAVE_FILE_PATH));
                    BufferedWriter writer = new BufferedWriter(new FileWriter(filePath))) {
                writer.write(winningPlayer + " won the game!\n");
                String line;
                while ((line = reader.readLine()) != null) {
                    writer.write(line);
                    writer.newLine();
                }
                System.out.println("Game saved to: " + filePath);
            } catch (IOException e) {
                e.printStackTrace();
                JOptionPane.showMessageDialog(null, "Error saving the game.", "Error", JOptionPane.ERROR_MESSAGE);
            }
        } else {
            System.out.println("File saving canceled.");
        }
    }
}
