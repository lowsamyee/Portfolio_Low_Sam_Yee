/*
 * The BoardView class represents the visual component of the game's board.
 * It displays a grid of buttons that represent cells on the board, along with row and column labels for navigation.
 * 
 * Design Pattern: Part of the MVC pattern, acting as a subcomponent of the View layer.
 * 
 * Composition:
 * - Contains a grid of buttons (`JButton`) to represent the board cells.
 * - Includes row and column labels for visual guidance.
 * - Managed by MainView as part of the overall game interface.
 * 
 * Features:
 * - Supports flipping the board view for a different perspective.
 * - Highlights cells for available moves and enemy-containing cells.
 * - Allows adding listeners to specific cells for user interactions.
 * - Updates cell content to reflect game pieces.
 * 
 * Written by: 
 * - Hoo Enn Xin 
 * - Low Sam Yee
 */
package view;

import javax.swing.*;

import model.Piece;

import java.awt.*;
import java.awt.event.ActionListener;
import java.util.ArrayList;

public class BoardView extends JPanel {
    private JButton[][] buttons; // 2D array of buttons to represent the board cells
    private JPanel gridPanel; // Panel for the board grid
    private JLabel[] rowLabels; // Labels for rows (1-8)
    private JLabel[] colLabels; // Labels for columns (a-e)
    private boolean isFlipped = false;

    private static final int ROWS = 8; // 8 rows
    private static final int COLS = 5; // 5 columns
    private static final int BUTTON_SIZE = 50; // Smaller button size for compact board
    private ArrayList<int[]> highlightedCells = new ArrayList<>();

    public BoardView() {
        this.setLayout(new BorderLayout());
        this.setBorder(BorderFactory.createEmptyBorder(20, 20, 20, 20)); // Padding around the board view

        initialBoard();
    }

    public void initialBoard() {

        gridPanel = new JPanel(new GridLayout(ROWS, COLS)); // Set layout to 8 rows and 5 columns
        buttons = new JButton[ROWS][COLS];
        rowLabels = new JLabel[ROWS];
        colLabels = new JLabel[COLS];

        for (int i = 0; i < ROWS; i++) {
            for (int j = 0; j < COLS; j++) {
                buttons[i][j] = new JButton("");
                buttons[i][j].setPreferredSize(new Dimension(BUTTON_SIZE, BUTTON_SIZE)); // Smaller size of each cell
                gridPanel.add(buttons[i][j]);
            }
        }
        // Create row labels (1-8)
        JPanel rowLabelPanel = new JPanel(new GridLayout(ROWS, 1));
        for (int i = 0; i < ROWS; i++) {
            rowLabels[i] = new JLabel(String.valueOf(i + 1), SwingConstants.CENTER);
            rowLabelPanel.add(rowLabels[i]);
        }

        // Create column labels (a-h)
        JPanel colLabelPanel = new JPanel(new GridLayout(1, COLS));
        for (int i = 0; i < COLS; i++) {
            colLabels[i] = new JLabel(String.valueOf((char) ('a' + i)), SwingConstants.CENTER);
            colLabelPanel.add(colLabels[i]);
        }

        // Add labels and grid to the main panel
        this.add(rowLabelPanel, BorderLayout.WEST); // Row labels on the left
        this.add(colLabelPanel, BorderLayout.NORTH); // Column labels on the top
        this.add(gridPanel, BorderLayout.CENTER); // Board grid in the center
    }

    public void flipBoardView() {
        isFlipped = !isFlipped;
        // Flip row labels (invert order)
        for (int i = 0; i < rowLabels.length / 2; i++) {
            String temp = rowLabels[i].getText();
            rowLabels[i].setText(rowLabels[rowLabels.length - 1 - i].getText());
            rowLabels[rowLabels.length - 1 - i].setText(temp);
        }

        // Reverse column labels (a-e -> e-a or vice versa)
        for (int i = 0; i < colLabels.length / 2; i++) {
            String temp = colLabels[i].getText();
            colLabels[i].setText(colLabels[colLabels.length - 1 - i].getText());
            colLabels[colLabels.length - 1 - i].setText(temp);
        }
        // Reverse the buttons' layout based on the flipped state
        JButton[][] flippedButtons = new JButton[ROWS][COLS];
        if (isFlipped) {
            // Create a flipped version of the button texts
            for (int i = 0; i < ROWS; i++) {
                for (int j = 0; j < COLS; j++) {
                    // Reverse rows and columns
                    int flippedRow = ROWS - 1 - i;
                    int flippedCol = COLS - 1 - j;

                    // Update button text and store in flippedButtons
                    flippedButtons[flippedRow][flippedCol] = buttons[i][j];
                }
            }
        } else {
            // Revert to the original layout when unflipping
            for (int i = 0; i < ROWS; i++) {
                for (int j = 0; j < COLS; j++) {
                    // Reverse rows and columns back to original positions
                    int originalRow = ROWS - 1 - i;
                    int originalCol = COLS - 1 - j;

                    // Restore the button text and store in flippedButtons
                    flippedButtons[originalRow][originalCol] = buttons[i][j];
                }
            }
        }

        // Update the `buttons` array with the new layout
        buttons = flippedButtons;

        // Refresh the grid panel
        gridPanel.revalidate();
        gridPanel.repaint();
    }

    // Add a listener to a specific cell
    public void addCellListener(int x, int y, ActionListener listener) {
        buttons[x][y].addActionListener(listener); // Add listener to each button
    }

    // Update a cell with the piece's name and color
    public void updateCell(int x, int y, Piece piece) {
        JButton cell = buttons[x][y];
        if (piece != null) {
            cell.setIcon(piece.getImage());
        } else {
            cell.setIcon(null);
        }
    }

    public void highlightAvailableMoves(ArrayList<int[]> availableMoves, ArrayList<int[]> moveContainEnemy) {
        // First clear previous highlights
        clearHighlights();

        // Store the highlighted cells for later use
        highlightedCells = availableMoves;

        // Change the background color of the buttons for the available moves
        for (int[] position : highlightedCells) {
            int x = position[0];
            int y = position[1];
            buttons[x][y].setBackground(Color.YELLOW); // Set background color to yellow for highlighting
        }
        // Highlight enemy-containing moves with red
        for (int[] position : moveContainEnemy) {
            int x = position[0];
            int y = position[1];
            buttons[x][y].setBackground(Color.RED); // Set background color to red for enemy-containing moves
        }
    }

    // Clear all highlights
    public void clearHighlights() {
        // Reset the background color of all buttons to default (can be transparent or
        // original color)
        for (int i = 0; i < ROWS; i++) {
            for (int j = 0; j < COLS; j++) {
                buttons[i][j].setBackground(null); // Reset to the default background color
            }
        }

        highlightedCells.clear();
    }

}