/*The Board class manages the chessboard's state, pieces, and transformations.

 Design pattern - 
1. Factory Pattern is used, allows encapsulate the creation logic 
 for different Piece types (Xor, Tor, Biz, Sau, Ram) in a centralized and avoid unwarranted if/else/switch statements.
2. Strategy pattern is used for the transformation logic of Tor and Xor pieces.

Delegation: Delegates piece creation to the PieceFactory interface via a map (pieceFactoryMap).
Composition: Board and Piece, if the Board is destroyed or reset, the Piece objects are also destroyed.
Single Responsibility Principle (SRP):The Board class is solely responsible for managing the pieces' positions.

Written by: Kah Wei */

package model;

import java.util.HashMap;
import java.util.Map;

interface PieceFactory {
    Piece create(String color, int x, int y);
}

public class Board {
    private Piece[][] board;
    private static final int rows = 8;
    private static final int cols = 5;
    private boolean isFlipped = false;
    private boolean isPieceCapture = false;
    private Piece capturedPiece = null;

    public Board() {
        this.board = new Piece[rows][cols]; // 5x8 grid for the board
        initialize(); // Call initialize() to set up the board
    }

    // Initialize the board with pieces
    public void initialize() {
        // Clear the board
        isFlipped = false;

        for (int row = 0; row < rows; row++) {
            for (int col = 0; col < cols; col++) {
                board[row][col] = null; // clear all positions
            }
        }

        // Piece order for the top (Red) and bottom (Blue) rows
        String[] pieceOrder = { "Xor", "Biz", "Sau", "Biz", "Tor" };

        // Create a HashMap to map piece types to their creation logic
        Map<String, PieceFactory> pieceFactoryMap = new HashMap<>();
        pieceFactoryMap.put("Xor", (c, x, y) -> new Xor(c, x, y));
        pieceFactoryMap.put("Biz", (c, x, y) -> new Biz(c, x, y));
        pieceFactoryMap.put("Sau", (c, x, y) -> new Sau(c, x, y));
        pieceFactoryMap.put("Tor", (c, x, y) -> new Tor(c, x, y));
        pieceFactoryMap.put("Ram", (c, x, y) -> new Ram(c, x, y));

        // Place top row (Red) and bottom row (Blue)
        java.util.stream.IntStream.of(0, 7) // Rows 0 (Red) and 7 (Blue)
                .forEach(row -> {
                    String color = (row == 0) ? "Red" : "Blue";
                    java.util.stream.IntStream.range(0, cols)
                            .mapToObj(col -> new Object[] { col, pieceFactoryMap.get(pieceOrder[col]) }) // Map column
                                                                                                         // to piece
                                                                                                         // factory
                            .filter(entry -> entry[1] != null) // Filter out invalid piece types
                            .forEach(entry -> {
                                int col = (int) entry[0];
                                PieceFactory factory = (PieceFactory) entry[1];
                                int adjustCol = (color.equals("Red")) ? cols - 1 - col : col; // Flip Red's column order
                                board[row][adjustCol] = factory.create(color, row, adjustCol); // Create and place the
                                                                                               // piece
                            });
                });

        // Place Ram pieces in rows 1 (Red) and 6 (Blue)
        for (int row : new int[] { 1, 6 }) { // Rows 1 and 6
            String color = (row == 1) ? "Red" : "Blue";
            for (int col = 0; col < cols; col++) {
                board[row][col] = pieceFactoryMap.get("Ram").create(color, row, col);
            }
        }
    }

    // Encapsulation, setter
    public boolean isFlipped() {
        return isFlipped;
    }

    // Encapsulation, getter
    public void flipBoard() {
        isFlipped = !isFlipped;
    }

    public int[] mapViewToBoardCoordinates(int x, int y) {
        if (isFlipped()) {
            // Translate the view coordinates to the flipped board's coordinates
            int flippedX = rows - 1 - x;
            int flippedY = cols - 1 - y;
            return new int[] { flippedX, flippedY };
        }
        // Return the original coordinates if the board is not flipped
        return new int[] { x, y };
    }

    // Encapsulation, getter
    public Piece getPiece(int x, int y) {
        return board[x][y]; // Return the piece at the given position
    }

    // Encapsulation, setter
    public void setPiece(int x, int y, Piece piece) {
        board[x][y] = piece; // Set a piece at the given position
    }

    public void transformTor() {
        // Iterate over the board and transform each Tor piece
        for (int row = 0; row < rows; row++) {
            for (int col = 0; col < cols; col++) {
                Piece piece = getPiece(row, col);
                if (piece instanceof Tor) {
                    // Replace the Tor piece with an Xor piece of the same color
                    setPiece(row, col, new Xor(piece.getColor(), row, col));
                }
            }
        }
    }

    // Transforms Tor pieces into Xor and vice versa, depending on the turn counter.
    public void transformTorXor() {
        // Create a HashMap to map piece types to their transformation logic
        Map<Class<? extends Piece>, String> transformationMap = new HashMap<>();
        transformationMap.put(Tor.class, "Xor");
        transformationMap.put(Xor.class, "Tor");

        // PieceFactory map for creating pieces
        Map<String, PieceFactory> pieceFactoryMap = new HashMap<>();
        pieceFactoryMap.put("Xor", (color, x, y) -> new Xor(color, x, y));
        pieceFactoryMap.put("Tor", (color, x, y) -> new Tor(color, x, y));

        // Iterate over the board and perform transformations
        for (int row = 0; row < rows; row++) {
            for (int col = 0; col < cols; col++) {
                Piece piece = getPiece(row, col);
                if (piece != null && transformationMap.containsKey(piece.getClass())) {
                    // Get the target piece type from the transformation map
                    String targetPieceType = transformationMap.get(piece.getClass());
                    PieceFactory factory = pieceFactoryMap.get(targetPieceType);
                    // Replace the current piece with the transformed piece
                    setPiece(row, col, factory.create(piece.getColor(), row, col));

                }
            }
        }
    }

    public boolean isWithinBounds(int x, int y) {
        return x >= 0 && x < 8 && y >= 0 && y < 5; // Update board dimensions as needed
    }

    public void movePiece(int toX, int toY, Piece fromPiece) {
        int fromX = fromPiece.getX();
        int fromY = fromPiece.getY();

        // Check if there is an enemy piece at the target position
        Piece targetPiece = board[toX][toY];
        if (targetPiece != null) {
            if (!targetPiece.getColor().equals(fromPiece.getColor())) {
                // Capture the enemy piece
                isPieceCapture = true;
                capturedPiece = targetPiece;
                board[toX][toY] = null; // Remove the enemy piece from the board
            } else {
                // Invalid move: cannot capture your own piece
                System.out.println("Invalid move: Target position contains your own piece!");
                return;
            }
        }

        // Remove the piece from its old position
        board[fromX][fromY] = null;

        // Update the piece's position
        fromPiece.setX(toX);
        fromPiece.setY(toY);

        // Add the piece to its new position
        board[toX][toY] = fromPiece;

    }

    public boolean isPieceCapture() {
        return isPieceCapture;
    }

    public Piece getCapturePiece() {
        return capturedPiece;
    }

    public int getRows() {
        return rows;
    }

    public int getCols() {
        return cols;
    }

}
