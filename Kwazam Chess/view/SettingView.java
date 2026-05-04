/*
 * The SettingView class represents a specific view in the game's graphical user interface
 * that provides settings-related actions, such as pausing, resuming, and resetting the game.
 * 
 * Design Pattern: Part of the MVC pattern, acting as a subcomponent of the View layer.
 * 
 * Composition:
 * - This class is part of the MainView and is managed by it. Its lifecycle is tied to MainView.
 * - Buttons for controlling game state (stop, reset, resume) are created and laid out using GridBagLayout.

 * Delegation:
 * - Game state actions (pause, reset, stop) are delegated to the MainView,
 *   which then communicates with the GameController.
 *   
 * Written by: Low Sam Yee
 */
package view;

import java.awt.*;

import javax.swing.*;

public class SettingView extends JPanel {
    private JButton stopGameButton, resetGameButton, resumeGameButton;

    public SettingView(MainView mainView) {
        this.setLayout(new GridBagLayout());
        GridBagConstraints gbc = new GridBagConstraints();

        // resume Game button
        resumeGameButton = new JButton("Resume Game");
        resumeGameButton.setPreferredSize(new Dimension(150, 50));
        resumeGameButton.setFont(new Font("Arial", Font.BOLD, 15));
        resumeGameButton.addActionListener(e -> mainView.resumeGame());

        // reset game button
        resetGameButton = new JButton("Reset Game");
        resetGameButton.setPreferredSize(new Dimension(150, 50));
        resetGameButton.setFont(new Font("Arial", Font.BOLD, 15));
        resetGameButton.addActionListener(e -> mainView.resetGame());

        // Stop Game button
        stopGameButton = new JButton("Stop Game");
        stopGameButton.setPreferredSize(new Dimension(150, 50));
        stopGameButton.setFont(new Font("Arial", Font.BOLD, 15));
        stopGameButton.addActionListener(e -> mainView.stopGame());

        // Configure GridBagConstraints for the button
        gbc.insets = new Insets(10, 10, 10, 10);
        gbc.gridx = 0;
        gbc.gridy = 0;
        gbc.fill = GridBagConstraints.CENTER;
        gbc.insets = new Insets(20, 20, 20, 20);

        this.add(resumeGameButton, gbc);
        // Configure GridBagConstraints for the checkbox
        gbc.gridy++;
        this.add(resetGameButton, gbc);
        gbc.gridy++;
        this.add(stopGameButton, gbc);

    }
}
