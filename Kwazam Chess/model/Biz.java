//The Biz class represents the "Biz" chess piece, 
//capable of moving in an L-shape like a knight.
//
//Subclassing: Inherits from Piece to define specific behavior for the "Biz" piece.
//
//Written by: Hui May
package model;

import java.util.ArrayList;

public class Biz extends Piece {

    public Biz(String colorTurn, int x, int y) {
        super("Biz", colorTurn, x, y); 
    }

    //Polymorphism:
    //Implements getAvailableMoves() to override the abstract method in Piece.
    @Override
    public ArrayList<int[]> getAvailableMoves(int x, int y, Board board) {
        ArrayList<int[]> availableMoves = new ArrayList<>();

        // All possible L-shape moves (3x2 or 2x3)
        int[][] moves = {
                { 2, 1 }, { 2, -1 }, { -2, 1 }, { -2, -1 }, // Horizontal 3x2
                { 1, 2 }, { 1, -2 }, { -1, 2 }, { -1, -2 } // Vertical 2x3
        };

        for (int[] move : moves) {
            int newX = x + move[0];
            int newY = y + move[1];

            // Check if the move is within bounds
            if (board.isWithinBounds(newX, newY)) {
                Piece pieceAtCell = board.getPiece(newX, newY);

                // Biz can skip over other pieces, so only check the destination cell
                if (pieceAtCell == null || !pieceAtCell.getColor().equalsIgnoreCase(this.getColor())) {
                    // Add the move if the destination cell is empty or occupied by an opponent
                    availableMoves.add(new int[] { newX, newY });
                }
            }
        }

        return availableMoves;
    }

}
