    <?php if (isset($user) && $user): ?>
                </main>
                
                <footer style="background-color: var(--surface); border-top: 1px solid var(--border); padding: var(--spacing-lg); margin-top: auto;">
                    <div class="text-center text-secondary">
                        <p>&copy; <?php echo date('Y'); ?> SkillOffice. All rights reserved.</p>
                    </div>
                </footer>
            </div>
        </div>
    <?php else: ?>
        <!-- No sidebar for non-authenticated pages -->
        </main>
        <footer style="background-color: var(--surface); border-top: 1px solid var(--border); padding: var(--spacing-lg); margin-top: var(--spacing-2xl);">
            <div class="container text-center text-secondary">
                <p>&copy; <?php echo date('Y'); ?> SkillOffice. All rights reserved.</p>
            </div>
        </footer>
    <?php endif; ?>
    
    <script src="<?php echo asset('js/app.js'); ?>"></script>
    <?php if (isset($additionalJS)): ?>
        <?php foreach ($additionalJS as $js): ?>
            <script src="<?php echo $js; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>

